<?php
/**
 * @namespace
 */
namespace Phire\Table;

use Pop\Db\Record;

class UserSessions extends Record
{

    /**
     * @var   string
     */
    protected $tableName = 'user_sessions';

    /**
     * @var   string
     */
    protected $primaryId = 'id';

    /**
     * @var   boolean
     */
    protected $auto = true;

    /**
     * @var   string
     */
    protected $prefix = DB_PREFIX;

    /**
     * Method is see if the session has expired.
     *
     * @param  int    $exp
     * @param  string $time
     * @return boolean
     */
    public function hasExpired($exp, $time)
    {
        $expired = false;

        if ((time() - strtotime($time)) > ($exp * 60)) {
            $expired = true;
        }

        return $expired;
    }

    /**
     * Static method to clear sessions
     *
     * @param  int $tid
     * @return int
     */
    public static function getCountOfType($tid)
    {
        $sql = static::getSql();
        $sql->select(array(
            0 => DB_PREFIX . 'user_sessions.id',
            4 => DB_PREFIX . 'user_sessions.user_id',
            7 => DB_PREFIX . 'users.type_id'
        ))->join(DB_PREFIX . 'users', array('user_id', 'id'), 'LEFT JOIN')
          ->join(DB_PREFIX . 'user_types', array(DB_PREFIX . 'users.type_id', 'id'), 'LEFT JOIN');

        $sql->select()->where()->equalTo('type_id', (int)$tid);

        $sessions = static::execute($sql->render(true));
        return $sessions->count();
    }

    /**
     * Static method to clear sessions
     *
     * @param  int $id
     * @return void
     */
    public static function clearSessions($id)
    {
        $sql = static::getSql();
        $sql->delete()
            ->where()->equalTo('user_id', ':user_id')
            ->lessThanOrEqualTo('start', ':start');

        static::execute(
            $sql->render(true),
            array(
                'user_id' => $id,
                'start'   => date('Y-m-d H:i:s', time() - 86400)
            )
        );
    }

}

