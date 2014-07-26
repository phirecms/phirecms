<?php
/**
 * @namespace
 */
namespace Phire\Model;

use Pop\Data\Type\Html;
use Phire\Table;

class UserSession extends AbstractModel
{

    /**
     * Get all roles method
     *
     * @param  string $sort
     * @param  string $page
     * @return void
     */
    public function getAll($sort = null, $page = null)
    {
        $order = $this->getSortOrder($sort, $page);
        $order['field'] = ($order['field'] == 'id') ? DB_PREFIX . 'user_sessions.id' : $order['field'];

        // Create SQL object to get session data
        $sql = Table\UserSessions::getSql();
        $sql->select(array(
            0 => DB_PREFIX . 'user_sessions.id',
            1 => DB_PREFIX . 'user_types.type',
            2 => DB_PREFIX . 'users.username',
            3 => DB_PREFIX . 'user_sessions.ip',
            4 => DB_PREFIX . 'user_sessions.user_id',
            5 => DB_PREFIX . 'user_sessions.ua',
            6 => DB_PREFIX . 'user_sessions.start',
            7 => DB_PREFIX . 'users.type_id'
        ))->join(DB_PREFIX . 'users', array('user_id', 'id'), 'LEFT JOIN')
          ->join(DB_PREFIX . 'user_types', array(DB_PREFIX . 'users.type_id', 'id'), 'LEFT JOIN')
          ->orderBy($order['field'], $order['order']);

        if (null !== $order['limit']) {
            $sql->select()->limit($order['limit'])
                          ->offset($order['offset']);
        }

        $searchByMarked = null;
        $searchByAry = array();
        $types = Table\UserTypes::findAll();
        foreach ($types->rows as $type) {
            $searchByAry[$type->id] = $type->type;
        }

        if (isset($_GET['search_by'])) {
            $count = Table\UserSessions::getCountOfType((int)$_GET['search_by']);
            $searchByMarked = (int)$_GET['search_by'];
            $sql->select()->where()->equalTo('type_id', (int)$_GET['search_by']);
        } else {
            $count = Table\UserSessions::getCount();
        }

        // Execute SQL query
        $sessions = Table\UserSessions::execute($sql->render(true));

        if ($this->data['acl']->isAuth('Phire\Controller\Phire\User\SessionsController', 'remove')) {
            $removeCheckbox = '<input type="checkbox" name="remove_sessions[]" id="remove_sessions[{i}]" value="[{id}]" />';
            $removeCheckAll = '<input type="checkbox" id="checkall" name="checkall" value="remove_sessions" />';
            $submit = array(
                'class' => 'remove-btn',
                'value' => $this->i18n->__('Remove')
            );
        } else {
            $removeCheckbox = '&nbsp;';
            $removeCheckAll = '&nbsp;';
            $submit = array(
                'class' => 'remove-btn',
                'value' => $this->i18n->__('Remove'),
                'style' => 'display: none;'
            );
        }

        if ($this->data['acl']->isAuth('Phire\Controller\Phire\User\IndexController', 'edit')) {
            $username = '<a href="' . BASE_PATH . APP_URI . '/users/edit/[{user_id}]">[{username}]</a>';
        } else {
            $username = '[{username}]';
        }

        $options = array(
            'form' => array(
                'id'      => 'session-remove-form',
                'action'  => BASE_PATH . APP_URI . '/users/sessions/remove',
                'method'  => 'post',
                'process' => $removeCheckbox,
                'submit'  => $submit
            ),
            'table' => array(
                'headers' => array(
                    'id'         => '<a href="' . BASE_PATH . APP_URI . '/users/sessions?sort=id">#</a>',
                    'type'       => '<a href="' . BASE_PATH . APP_URI . '/users/sessions?sort=type">' . $this->i18n->__('Type') . '</a>',
                    'username'   => '<a href="' . BASE_PATH . APP_URI . '/users/sessions?sort=type">' . $this->i18n->__('Username') . '</a>',
                    'ip'         => $this->i18n->__('IP'),
                    'ua'         => $this->i18n->__('User Agent'),
                    'started'    => '<a href="' . BASE_PATH . APP_URI . '/users/sessions?sort=start">' . $this->i18n->__('Started') . '</a>',
                    'process'    => $removeCheckAll
                ),
                'class'       => 'data-table',
                'cellpadding' => 0,
                'cellspacing' => 0,
                'border'      => 0,
            ),
            'separator' => '',
            'date'      => $this->config->datetime_format,
            'exclude'   => array(
                'type_id', 'user_id', 'start', 'process' => array('id' => $this->data['user']->sess_id)
            ),
            'username'  => $username,
            'indent'    => '        '
        );

        $sessAry = array();
        foreach ($sessions->rows as $session) {
            $session->started = date($this->config->datetime_format, strtotime($session->start)) . ' (' . \Pop\Feed\Format\AbstractFormat::calculateTime($session->start) . ')';
            $sessAry[] = $session;
        }

        if (isset($sessAry[0])) {
            $this->data['table'] = Html::encode($sessAry, $options, $this->config->pagination_limit, $this->config->pagination_range, $count);
            $this->data['searchBy']  = new \Pop\Form\Element\Select('search_by', $searchByAry, $searchByMarked);
        }
    }

    /**
     * Remove content navigation
     *
     * @param  array   $post
     * @return void
     */
    public function remove(array $post)
    {
        if (isset($post['remove_sessions'])) {
            foreach ($post['remove_sessions'] as $id) {
                $session = Table\UserSessions::findById($id);
                if (isset($session->id)) {
                    $session->delete();
                }
            }
        }
    }

}

