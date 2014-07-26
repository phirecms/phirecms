<?php
/**
 * @namespace
 */
namespace Phire;

class Cli
{

    /**
     * CLI arguments
     * @var array
     */
    protected $args = null;

    /**
     * CLI commands
     * @var array
     */
    protected $commands = array(
        'help',
        'config',
        'version',
        'user',
        'ext',
        'install',
        'update',
        'archive'
    );
    /**
     *
     * CLI command arguments
     * @var array
     */
    protected $arguments = array(
        'user'   => array(
            'types',
            'roles',
            'add',
            'password',
            'role',
            'remove',
            'list',
            'kill',
            'sessions'
        ),
        'ext'    => array(
            'install',
            'list',
            'activate',
            'deactivate',
            'remove'
        ),
        'update'    => array(
            'system',
            'module'
        )
    );

    /**
     * Constructor method to instantiate the CLI object
     *
     * @param  array $args
     * @return self
     */
    public function __construct($args = array())
    {
        $this->args = $args;

        if (isset($this->args[1]) && ($this->args[1] == 'help')) {
            $this->help();
            exit();
        } else if (isset($this->args[1]) && !in_array($this->args[1], $this->commands)) {
            echo '  The command \'' . $this->args[1] . '\' was not recognized. Use ./phire help for help.' . PHP_EOL . PHP_EOL;
            exit();
        }

        if (isset($this->args[1]) && ($this->args[1] != 'install') && !Project::isInstalled(true)) {
            echo '  Phire CMS 2 does not appear to be installed. Please check the config file or install the application.' . PHP_EOL . PHP_EOL;
            exit();
        } else if (isset($this->args[1]) && ($this->args[1] == 'install') && Project::isInstalled(true)) {
            echo '  Phire CMS 2 appears to already be installed.' . PHP_EOL . PHP_EOL;
            exit();
        } else {
            if (isset($this->args[1])) {
                switch ($this->args[1]) {
                    case 'config':
                        $this->config();
                        break;

                    case 'version':
                        $this->version();
                        break;

                    case 'user':
                        $this->user();
                        break;

                    case 'ext':
                        $this->ext();
                        break;

                    case 'install':
                        $this->install();
                        break;

                    case 'update':
                        $this->update();
                        break;
                }
            }
        }
    }

    /**
     * Show help
     *
     * @return void
     */
    protected function help()
    {
        echo file_get_contents(__DIR__ . '/../../data/cli-help.txt');
    }

    /**
     * Show config
     *
     * @return void
     */
    protected function config()
    {
        echo 'Current Configuration' . PHP_EOL;
        echo '---------------------' . PHP_EOL;
        echo PHP_EOL;
        $config = new Model\Config();
        $config->getAll();

        foreach ($config->config->server as $key => $value) {
            if (!empty($value)) {
                $name = ucwords(str_replace(array('_', 'php'), array(' ', 'PHP'), $key));
                if (($key == 'system_base_path') && !empty($config->config->server['system_document_root'])) {
                    $value = str_replace($config->config->server['system_document_root'], '', $value);
                }
                echo '  ' . $name . ': ' . str_repeat(' ', (30 - strlen($name))) . $value . PHP_EOL;
            }
        }
        echo PHP_EOL;
    }

    /**
     * Show version
     *
     * @return void
     */
    protected function version()
    {
        $latest = 'N/A';
        $handle = fopen('http://update.phirecms.org/system/version', 'r');
        if ($handle !== false) {
            $latest = trim(stream_get_contents($handle));
            fclose($handle);
        }

        echo 'Version' . PHP_EOL;
        echo '-------' . PHP_EOL;
        echo PHP_EOL;
        echo '  Current Installed: ' . Project::VERSION . PHP_EOL;
        echo '  Latest Available:  ' . $latest . PHP_EOL;
        echo PHP_EOL;
        echo (version_compare(Project::VERSION, $latest) >= 0) ?
            '  The latest version is installed.' :
            '  A newer version is available.';
        echo PHP_EOL . PHP_EOL;
    }

    /**
     * User commands
     *
     * @return void
     */
    protected function user()
    {
        echo 'Users' . PHP_EOL;
        echo '-----' . PHP_EOL;
        echo PHP_EOL;

        if (!isset($this->args[2])) {
            $this->argNotFound('user');
        } else if (!in_array($this->args[2], $this->arguments['user'])) {
            $this->argInvalid('user', $this->args[2]);
        } else {
            switch ($this->args[2]) {
                // Show user types
                case 'types':
                    echo '  User Types:' . PHP_EOL;
                    echo '  ===========' . PHP_EOL . PHP_EOL;
                    $types = Table\UserTypes::findAll('id ASC');
                    echo "  ID# \tType" . PHP_EOL;
                    echo "  ----\t----" . PHP_EOL;
                    if (isset($types->rows[0])) {
                        foreach ($types->rows as $type) {
                            echo "  " . $type->id . "\t" . $type->type . PHP_EOL;
                        }
                    } else {
                        echo "  There are currently no user types." . PHP_EOL;
                    }
                    echo PHP_EOL;
                    break;

                // Show user roles
                case 'roles':
                    echo '  User Roles:' . PHP_EOL;
                    echo '  ===========' . PHP_EOL . PHP_EOL;
                    $roles = Table\UserRoles::findAll('type_id, id ASC');
                    echo "  ID# \tType\tRole" . PHP_EOL;
                    echo "  ----\t----\t----" . PHP_EOL;
                    if (isset($roles->rows[0])) {
                        foreach ($roles->rows as $role) {
                            if ((int)$role->type_id != 0) {
                                $type = Table\UserTypes::findById($role->type_id);
                                $typeName = (isset($role->id)) ? $type->type : '(N/A)';
                            } else {
                                $typeName = '(N/A)';
                            }
                            echo "  " . $role->id . "\t" . $typeName . "\t" . $role->name . PHP_EOL;
                        }
                    } else {
                        echo "  There are currently no user roles." . PHP_EOL;
                    }
                    echo PHP_EOL;
                    break;

                // Change a user's role
                case 'role':
                    echo '  Change User Role:' . PHP_EOL;
                    echo '  =================' . PHP_EOL . PHP_EOL;
                    $userId = self::cliInput('  Enter User ID#: ');
                    $user = Table\Users::findById($userId);
                    if (isset($user->id)) {
                        $roles = Table\UserRoles::findAll('id ASC', array('type_id' => $user->type_id));
                        echo PHP_EOL . '  User Roles' . PHP_EOL;
                        echo '  ----------' . PHP_EOL;
                        echo "  0   \t(Blocked)" . PHP_EOL;
                        $roleIds = array(0);
                        $roleId = -1;
                        foreach ($roles->rows as $role) {
                            echo "  " . $role->id . "\t" . $role->name . PHP_EOL;
                            $roleIds[] = $role->id;
                        }
                        echo PHP_EOL;
                        while (!in_array($roleId, $roleIds)) {
                            $roleId = self::cliInput('  Enter Role ID#: ');
                        }
                        $user->role_id = ((int)$roleId > 0) ? (int)$roleId : null;
                        $user->update();
                        echo '  The user role has been changed.' . PHP_EOL;
                    } else {
                        echo '  The user ID ' . $userId . ' was not found.' . PHP_EOL;
                    }
                    break;

                // Add a user
                case 'add':
                    echo '  Add User:' . PHP_EOL;
                    echo '  =========' . PHP_EOL;
                    $types = Table\UserTypes::findAll('id ASC');
                    echo PHP_EOL . '  User Types' . PHP_EOL;
                    echo '  ----------' . PHP_EOL;
                    $typeIds = array(0);
                    $typeId = -1;
                    foreach ($types->rows as $type) {
                        echo "  " . $type->id . "\t" . $type->type . PHP_EOL;
                        $typeIds[] = $type->id;
                    }
                    echo PHP_EOL;
                    while (!in_array($typeId, $typeIds)) {
                        $typeId = self::cliInput('  Enter User Type ID#: ');
                    }

                    $roles = Table\UserRoles::findAll('id ASC', array('type_id' => $typeId));
                    echo PHP_EOL . '  User Roles' . PHP_EOL;
                    echo '  ----------' . PHP_EOL;
                    echo "  0   \t(Blocked)" . PHP_EOL;
                    $roleIds = array(0);
                    $roleId = -1;
                    foreach ($roles->rows as $role) {
                        echo "  " . $role->id . "\t" . $role->name . PHP_EOL;
                        $roleIds[] = $role->id;
                    }
                    echo PHP_EOL;
                    while (!in_array($roleId, $roleIds)) {
                        $roleId = self::cliInput('  Enter Role ID#: ');
                    }
                    $type = Table\UserTypes::findById($typeId);

                    $user = array(
                        'type_id'         => $typeId,
                        'role_id'         => (((int)$roleId > 0) ? (int)$roleId : null),
                        'email'           => null,
                        'username'        => null,
                        'password'        => null,
                        'verified'        => 1,
                        'failed_attempts' => 0,
                        'site_ids'        => 'a:1:{i:0;i:0;}'
                    );

                    $user['email']    = self::cliInput('  Enter User Email: ');
                    if (!$type->email_as_username) {
                        $user['username']  = self::cliInput('  Enter Username: ');
                    }  else {
                        $user['username'] = $user['email'];
                    }

                    $dupe = Table\Users::findBy(array('username' => $user['username']));

                    while (isset($dupe->id)) {
                        echo PHP_EOL . '  That username already exists. Please choose another username.' . PHP_EOL . PHP_EOL;
                        $user['email']    = self::cliInput('  Enter User Email: ');
                        if (!$type->email_as_username) {
                            $user['username']  = self::cliInput('  Enter Username: ');
                        }  else {
                            $user['username'] = $user['email'];
                        }
                        $dupe = Table\Users::findBy(array('username' => $user['username']));
                    }

                    $user['password'] = self::cliInput('  Enter Password: ');
                    $user['password'] = Model\User::encryptPassword($user['password'], $type->password_encryption);
                    $u = new Table\Users($user);
                    $u->save();
                    echo '  The new user has been added.' . PHP_EOL;
                    break;

                // Change a user's password
                case 'password':
                    echo '  Change User Password:' . PHP_EOL;
                    echo '  =====================' . PHP_EOL . PHP_EOL;
                    $userId = self::cliInput('  Enter User ID#: ');
                    $user = Table\Users::findById($userId);
                    if (isset($user->id)) {
                        $type = Table\UserTypes::findById($user->type_id);
                        $password = self::cliInput('  Enter New Password: ');
                        $user->password = Model\User::encryptPassword($password, $type->password_encryption);
                        $user->update();
                        echo '  The user password has been changed.' . PHP_EOL;
                    } else {
                        echo '  The user ID ' . $userId . ' was not found.' . PHP_EOL;
                    }
                    break;

                // List users
                case 'list':
                    $users = Table\Users::findAll('type_id, id ASC');
                    echo "  ID# \tType\t\tRole\t\tUsername\tEmail" . PHP_EOL;
                    echo "  ----\t----\t\t----\t\t--------\t-----" . PHP_EOL;
                    if (isset($users->rows[0])) {
                        foreach ($users->rows as $user) {
                            if ((int)$user->role_id != 0) {
                                $role = Table\UserRoles::findById($user->role_id);
                                $roleName = (isset($role->id)) ? $role->name : '(N/A)';
                            } else {
                                $roleName = '(Blocked)';
                            }
                            if ((int)$user->type_id != 0) {
                                $type = Table\UserTypes::findById($user->type_id);
                                $typeName = (isset($type->id)) ? $type->type : '(N/A)';
                            } else {
                                $typeName = '(N/A)';
                            }
                            $username = $user->username;
                            if (strlen($user->username) < 8) {
                                $username .= str_repeat(' ', 8 - strlen($user->username));
                            }
                            if (strlen($roleName) < 8) {
                                $roleName .= str_repeat(' ', 8 - strlen($roleName));
                            }
                            if (strlen($typeName) < 8) {
                                $typeName .= str_repeat(' ', 8 - strlen($typeName));
                            }
                            echo "  " . $user->id . "\t" . $typeName . "\t" . $roleName . "\t" . $username . "\t" . $user->email . PHP_EOL;
                        }
                    } else {
                        echo "  There are currently no users." . PHP_EOL;
                    }
                    echo PHP_EOL;
                    break;

                // List user sessions
                case 'sessions':
                    echo '  User Sessions:' . PHP_EOL;
                    echo '  ==============' . PHP_EOL . PHP_EOL;
                    $sessions = Table\UserSessions::findAll('id ASC');
                    if (isset($sessions->rows[0])) {
                        echo "  ID# \tUsername\tIP  \t\tBrowser\t\tStart" . PHP_EOL;
                        echo "  ----\t--------\t----\t\t-------\t\t-----" . PHP_EOL;
                        foreach ($sessions->rows as $session) {
                            $user = Table\Users::findById($session->user_id);
                            $username = (isset($user->id)) ? $user->username : '(N/A)';
                            if (strlen($username) < 8) {
                                $username .= str_repeat(' ', 8 - strlen($username));
                            }
                            $browser = '(N/A)   ';
                            if (stripos($session->ua, 'firefox') !== false) {
                                $browser = 'Firefox';
                            } else if (stripos($session->ua, 'chrome') !== false) {
                                $browser = 'Chrome';
                            } else if (stripos($session->ua, 'safari') !== false) {
                                $browser = 'Safari';
                            } else if ((stripos($session->ua, 'msie') !== false) || (stripos($session->ua, 'trident') !== false)) {
                                $browser = 'MSIE';
                            }
                            echo "  " . $session->id . "\t" . $username . "\t" . $session->ip . "\t" . $browser . "\t\t" . date('M d Y H:i:s', strtotime($session->start)) . PHP_EOL;
                        }
                        echo PHP_EOL;
                    } else {
                        echo "  There are currently no user sessions." . PHP_EOL . PHP_EOL;
                    }
                    break;

                // Remove a user
                case 'remove':
                    echo '  Remove User:' . PHP_EOL;
                    echo '  ============' . PHP_EOL . PHP_EOL;
                    $id = self::cliInput('  Enter User ID#: ');
                    $user = Table\Users::findById($id);
                    if (isset($user->id)) {
                        $sessions = new Table\UserSessions();
                        $sessions->delete(array('user_id' => $id));
                        Model\FieldValue::remove($id);
                        $user->delete();
                        echo '  The user ID ' . $id . ' has been removed.' . PHP_EOL;
                    } else {
                        echo '  The user ID ' . $id . ' was not found.' . PHP_EOL;
                    }
                    break;

                // Remove a user's session
                case 'kill':
                    echo '  Remove User Session:' . PHP_EOL;
                    echo '  ====================' . PHP_EOL . PHP_EOL;
                    $id = self::cliInput('  Enter User Session ID#: ');
                    $sess = Table\UserSessions::findById($id);
                    if (isset($sess->id)) {
                        $sess->delete();
                        echo '  The user session ID ' . $id . ' has been removed.' . PHP_EOL . PHP_EOL;
                    } else {
                        echo '  The user session ID ' . $id . ' was not found.' . PHP_EOL . PHP_EOL;
                    }
                    break;
            }
        }
    }

    /**
     * Extension commands
     *
     * @return void
     */
    protected function ext()
    {
        echo 'Extensions' . PHP_EOL;
        echo '----------' . PHP_EOL;
        echo PHP_EOL;

        if (!isset($this->args[2])) {
            $this->argNotFound('ext');
        } else if (!in_array($this->args[2], $this->arguments['ext'])) {
            $this->argInvalid('ext', $this->args[2]);
        } else {
            switch ($this->args[2]) {
                case 'install':
                    echo '  Extension Installation:' . PHP_EOL;
                    echo '  =======================' . PHP_EOL . PHP_EOL;

                    $modules = new Model\Extension();
                    $modules->getModules();

                    echo '  Installing Modules(s):' . PHP_EOL;
                    echo '  ----------------------' . PHP_EOL . PHP_EOL;
                    if (count($modules->new) > 0) {
                        echo '  ' . count($modules->new) . ' module(s) available for installation.' . PHP_EOL;
                        echo '  Installing...';
                        $modules->installModules();
                        echo 'OK!' . PHP_EOL;
                    } else {
                        echo '  There are no modules available for installation.' . PHP_EOL;
                    }
                    echo PHP_EOL;

                    break;
                case 'list':
                    $modules = new Model\Extension();
                    $modules->getModules();

                    $installModules = null;
                    if (count($modules->new) > 0) {
                        $installModules = ' (' . count($modules->new) . ' Available for Install) ';
                    }

                    echo '  Modules:' . $installModules . PHP_EOL;
                    echo '  ========' . PHP_EOL . PHP_EOL;
                    echo "  ID# \t\tName\t\tActive" . PHP_EOL;
                    echo "  ----\t\t----\t\t------" . PHP_EOL;
                    if (isset($modules->modules[0])) {
                        foreach ($modules->modules as $module) {
                            $moduleName = $module->name;
                            if (strlen($moduleName) < 8) {
                                $moduleName .= str_repeat(' ', 8 - strlen($moduleName));
                            }
                            echo "  " . $module->id . "\t\t" . $moduleName . "\t" . (($module->active) ? "Yes" : "No") . PHP_EOL;
                        }
                    } else {
                        echo "  There are currently no modules." . PHP_EOL;
                    }
                    echo PHP_EOL;
                    break;
                case 'activate':
                    echo '  Activate an Extension:' . PHP_EOL;
                    echo '  ======================' . PHP_EOL . PHP_EOL;
                    $id = self::cliInput('  Enter Extension ID#: ');
                    $ext = Table\Extensions::findById($id);
                    if (isset($ext->id)) {
                        if (!$ext->type) {
                            $exts = Table\Extensions::findAll(null, array('type' => 0));
                            foreach ($exts->rows as $ex) {
                                $e = Table\Extensions::findById($ex->id);
                                if (isset($e->id)) {
                                    $e->active = 0;
                                    $e->update();
                                }
                            }
                        }
                        $ext->active = 1;
                        $ext->update();
                        echo '  The extension ID ' . $id . ' has been activated.' . PHP_EOL;
                    } else {
                        echo '  The extension ID ' . $id . ' was not found.' . PHP_EOL;
                    }
                    echo PHP_EOL;
                    break;
                case 'deactivate':
                    echo '  Deactivate an Extension:' . PHP_EOL;
                    echo '  ========================' . PHP_EOL . PHP_EOL;
                    $id = self::cliInput('  Enter Extension ID#: ');
                    $ext = Table\Extensions::findById($id);
                    if (isset($ext->id)) {
                        $ext->active = 0;
                        $ext->update();
                        echo '  The extension ID ' . $id . ' has been deactivated.' . PHP_EOL;
                    } else {
                        echo '  The extension ID ' . $id . ' was not found.' . PHP_EOL;
                    }
                    echo PHP_EOL;
                    break;
                case 'remove':
                    echo '  Remove an Extension:' . PHP_EOL;
                    echo '  ====================' . PHP_EOL . PHP_EOL;
                    $id = self::cliInput('  Enter Extension ID#: ');
                    $ext = Table\Extensions::findById($id);
                    if (isset($ext->id)) {
                        $ex = new Model\Extension();
                        $ex->processModules(array('remove_modules' => array($id)));
                        echo '  The extension ID ' . $id . ' has been removed.' . PHP_EOL;
                    } else {
                        echo '  The extension ID ' . $id . ' was not found.' . PHP_EOL;
                    }
                    echo PHP_EOL;
                    break;
            }
        }
    }

    /**
     * Install command
     *
     * @return void
     */
    protected function install()
    {
        if (!is_writable(PH_CLI_ROOT . '/config.php')) {
            echo '  The configuration file is not writable. Please make it writable before continuing.' . PHP_EOL . PHP_EOL;
        } else {
            // Install config file and database
            $input = array(
                'language'            => null,
                'db_adapter'          => null,
                'db_name'             => null,
                'db_username'         => null,
                'db_password'         => null,
                'db_host'             => ((defined('DB_HOST') && (DB_HOST != '')) ? DB_HOST : 'localhost'),
                'db_prefix'           => ((defined('DB_PREFIX') && (DB_HOST != '')) ? DB_PREFIX : 'ph_'),
                'app_uri'             => (defined('APP_URI') ? APP_URI : '/phire'),
                'content_path'        => (defined('CONTENT_PATH') ? CONTENT_PATH : '/phire-content'),
                'password_encryption' => 4
            );

            $langs     = \Pop\I18n\I18n::getLanguages();
            $langKeys  = array_keys($langs);
            $langsList = null;

            $i = 1;
            foreach ($langs as $key => $value) {
                $num = ($i < 10) ? ' ' . $i : $i;
                $langsList .= '  ' . $num . ' : [' . $key . '] ' . $value . PHP_EOL;
                $i++;
            }

            $db = array(
                'Mysqli',
                'Pdo\Mysql',
                'Pdo\Pgsql',
                'Pdo\Sqlite',
                'Pgsql',
                'Sqlite'
            );

            echo 'Installation' . PHP_EOL;
            echo '------------' . PHP_EOL;
            echo PHP_EOL;

            echo '  Select Language:' . PHP_EOL . PHP_EOL;
            echo $langsList . PHP_EOL;
            echo PHP_EOL;

            $inputLang = -1;

            while (!isset($langKeys[$inputLang])) {
                $inputLang = self::cliInput('  Enter Language # (Enter for English): ');
                if (empty($inputLang)) {
                    $inputLang = 3;
                } else {
                    $inputLang--;
                }
            }

            $input['language'] = $langKeys[$inputLang];

            echo PHP_EOL . '  Select DB Adapter:' . PHP_EOL . PHP_EOL;
            foreach ($db as $key => $value) {
                echo  '  ' . ($key + 1) . ' : ' . $value . PHP_EOL;
            }
            echo PHP_EOL;

            $inputDb = -1;

            while (!isset($db[$inputDb])) {
                $inputDb = self::cliInput('  Enter DB Adapter #: ');
                $inputDb--;
            }

            $input['db_adapter'] = $db[$inputDb];

            if (stripos($input['db_adapter'], 'sqlite') === false) {
                $input['db_name'] = self::cliInput('  DB Name: ');
                $input['db_username'] = self::cliInput('  DB Username: ');
                $input['db_password'] = self::cliInput('  DB Password: ');

                $inputHost = self::cliInput('  DB Host (Enter for \'' . $input['db_host'] . '\'): ');
                $input['db_host'] = (empty($inputHost)) ? 'localhost' : $inputHost;
            }

            $inputPrefix = self::cliInput('  DB Prefix (Enter for \'' . $input['db_prefix'] . '\'): ');
            $input['db_prefix'] = (empty($inputPrefix)) ? $input['db_prefix'] : $inputPrefix;

            $inputAppUri = self::cliInput('  Application URI (Enter for \'' . $input['app_uri'] . '\'): ');
            $input['app_uri'] = (empty($inputAppUri)) ? $input['app_uri'] : $inputAppUri;

            $inputContentPath = self::cliInput('  Content Path (Enter for \'' . $input['content_path'] . '\'): ');
            $input['content_path'] = (empty($inputContentPath)) ? $input['content_path'] : $inputContentPath;

            // Check the content directory
            if (!file_exists(PH_CLI_ROOT . $input['content_path'])) {
                echo PHP_EOL . '  The content directory does not exist.' . PHP_EOL . PHP_EOL;
                exit();
            } else {
                $checkDirs = Project::checkDirs(PH_CLI_ROOT . $input['content_path'], true);
                if (count($checkDirs) > 0) {
                    echo PHP_EOL . '  The content directory (or subdirectories) are not writable.' . PHP_EOL . PHP_EOL;
                    exit();
                }
            }

            echo PHP_EOL . '  ...Checking Database...';

            if (stripos($input['db_adapter'], 'sqlite') === false) {
                $oldError = ini_get('error_reporting');
                error_reporting(E_ERROR);

                $dbCheck = \Pop\Project\Install\Dbs::check(array(
                    'database' => $input['db_name'],
                    'username' => $input['db_username'],
                    'password' => $input['db_password'],
                    'host'     => $input['db_host'],
                    'type'     => str_replace('\\', '_', $input['db_adapter']),
                ));
                error_reporting($oldError);

                if (null != $dbCheck) {
                    echo PHP_EOL . PHP_EOL . '  ' . wordwrap($dbCheck, 70, PHP_EOL . '  ') . PHP_EOL . PHP_EOL;
                    echo '  Please try again.' . PHP_EOL . PHP_EOL;
                    exit();
                }
            }

            echo '..OK!' . PHP_EOL . '  ...Installing Database...';

            $install = $install = new Model\Install();
            $install->config(new \ArrayObject($input, \ArrayObject::ARRAY_AS_PROPS), realpath(PH_CLI_ROOT));

            // Install initial user
            echo 'OK!' . PHP_EOL . PHP_EOL . '  Initial User Setup:' . PHP_EOL . PHP_EOL;
            $user = array(
                'email'    => null,
                'username' => null,
                'password' => null,
            );

            $email    = '';
            $username = '';
            $password = '';

            $emailValidator    = new \Pop\Validator\Email();
            $usernameValidator = new \Pop\Validator\AlphaNumeric();

            while (!$emailValidator->evaluate($email)) {
                $email = self::cliInput('  Enter User Email: ');
            }

            while ((strlen($username) < 4) || (!$usernameValidator->evaluate($username))) {
                $username = self::cliInput('  Enter Username (> 4 characters): ');
            }

            while (strlen($password) < 6) {
                $password = self::cliInput('  Enter Password (> 6 characters): ');
            }

            $user['email']    = $email;
            $user['username'] = $username;
            $user['password'] = $password;

            echo PHP_EOL . '  ...Saving Initial User...' . PHP_EOL . PHP_EOL;

            if (stripos($input['db_adapter'], 'Pdo') !== false) {
                $dbInterface = 'Pdo';
                $dbType = substr($input['db_adapter'], strpos($input['db_adapter'], '\\') + 1);
            } else {
                $dbInterface = $input['db_adapter'];
                $dbType = null;
            }

            if (stripos($input['db_adapter'], 'sqlite') !== false) {
                $input['db_name'] = PH_CLI_ROOT . $input['content_path'] . '/.htphire.sqlite';
                chmod(realpath(PH_CLI_ROOT . $input['content_path'] . '/.htphire.sqlite'), 0777);
            }

            $db = \Pop\Db\Db::factory($dbInterface, array(
                'type'     => $dbType,
                'database' => $input['db_name'],
                'host'     => $input['db_host'],
                'username' => $input['db_username'],
                'password' => $input['db_password']
            ));

            $db->adapter()->query("INSERT INTO " . $input['db_prefix'] . "users (type_id, role_id, username, password, email, verified, failed_attempts, site_ids, created) VALUES (2001, 3001, '" . $user['username'] . "', '" . Model\User::encryptPassword($user['password'], 4) . "', '" . $user['email'] ."', 1, 0, '" . serialize(array(0)) . "', '" . date('Y-m-d H:i:s') . "')");
            $db->adapter()->query('UPDATE ' . $input['db_prefix'] .'config SET value = \'' . $user['email'] . '\' WHERE setting = \'system_email\'');
        }
    }

    /**
     * Post install command
     *
     * @return void
     */
    public static  function postInstall()
    {
        try {
            $ext = new Model\Extension();
            $ext->getModules();

            if (count($ext->new) > 0) {
                $ext->installModules();
            }

            $dir = new \Pop\File\Dir(__DIR__ . '/../../../../..' . CONTENT_PATH . '/extensions/modules', true, true);
            foreach ($dir->getFiles() as $file) {
                chmod($file, 0777);
            }

            $dir = new \Pop\File\Dir(__DIR__ . '/../../../../..' . CONTENT_PATH . '/extensions/themes', true, true);
            foreach ($dir->getFiles() as $file) {
                chmod($file, 0777);
            }

            echo '  Installation Complete!' . PHP_EOL . PHP_EOL;
        } catch (\Exception $e) {
            echo '  ' . $e->getMessage() . PHP_EOL . PHP_EOL;
        }
    }

    /**
     * Perform update
     *
     * @return void
     */
    protected function update()
    {
        echo 'System Update' . PHP_EOL;
        echo '-------------' . PHP_EOL;
        echo PHP_EOL;

        if (!isset($this->args[2])) {
            $this->argNotFound('update');
        } else if (!in_array($this->args[2], $this->arguments['update'])) {
            $this->argInvalid('update', $this->args[2]);
        } else {
            $type    = $this->args[2];
            $name    = (isset($this->args[3])) ? $this->args[3] : 'phire';
            $version = null;
            if ($type == 'system') {
                $version = \Phire\Project::VERSION;
            } else {
                $ext = Table\Extensions::findBy(array('name' => $name));
                if (isset($ext->id)) {
                    $assets = unserialize($ext->assets);
                    $version = $assets['info']['Version'];
                }
            }

            $format  = null;
            $formats = \Pop\Archive\Archive::formats();
            if (isset($formats['zip'])) {
                $format = 'zip';
            } else if (isset($formats['tar']) && isset($formats['gz'])) {
                $format = 'tar.gz';
            }

            $writable = false;
            if (($type == 'system') && is_writable(__DIR__ . '/../../../../')) {
                $writable = true;
            } else if (($type == 'module') && is_writable(__DIR__ . '/../../../../../' . CONTENT_PATH . DIRECTORY_SEPARATOR . 'extensions' . DIRECTORY_SEPARATOR . 'modules')) {
                $writable = true;
            }

            if ($writable) {
                $config = new Model\Config();
                $config->getUpdate(array(
                    'type'    => $type,
                    'name'    => $name,
                    'version' => $version,
                    'format'  => $format
                ), true);
                if (null !== $config->error) {
                    echo '  ' . strip_tags($config->error) . PHP_EOL . PHP_EOL;
                } else {
                    echo '  ' . strip_tags($config->msg) . PHP_EOL . PHP_EOL;
                }
            } else {
                echo '  The ' . $type . ' folder must be writable to perform the update.' . PHP_EOL . PHP_EOL;
            }
        }
    }

    /**
     * Message for argument not found
     *
     * @param  string $cmd
     * @return void
     */
    protected function argNotFound($cmd)
    {
        echo '  You must pass an argument with the \'' . $cmd . '\' command.' . PHP_EOL;
        echo '  Available arguments for the \'' . $cmd . '\' command are: ' . PHP_EOL . PHP_EOL;
        echo '     ' . implode(', ', $this->arguments[$cmd]) . PHP_EOL . PHP_EOL;
        echo '  Use ./phire help for help.' . PHP_EOL . PHP_EOL;
    }

    /**
     * Message for invalid argument
     *
     * @param  string $cmd
     * @param  string $arg
     * @return void
     */
    protected function argInvalid($cmd, $arg)
    {
        echo '  The argument \'' . $arg . '\' was not recognized.' . PHP_EOL;
        echo '  Available arguments for the \'' . $cmd . '\' command are: ' . PHP_EOL . PHP_EOL;
        echo '    ' . implode(', ', $this->arguments[$cmd]) . PHP_EOL . PHP_EOL;
        echo '  Use ./phire help for help.' . PHP_EOL . PHP_EOL;
    }

    /**
     * Return the input from STDIN
     *
     * @param  string $msg
     * @return string
     */
    protected static function cliInput($msg = null)
    {
        echo ((null === $msg) ? \Pop\I18n\I18n::factory()->__('Continue?') . ' (Y/N) ' : $msg);
        $input = null;

        while (null === $input) {
            $prompt = fopen("php://stdin", "r");
            $input = fgets($prompt);
            $input = rtrim($input);
            fclose ($prompt);
        }

        return $input;
    }

}

