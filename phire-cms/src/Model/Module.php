<?php
/**
 * Phire CMS (http://www.phirecms.org/)
 *
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 */

/**
 * @namespace
 */
namespace Phire\Model;

use Phire\Table;

/**
 * Module model class
 *
 * @category   Phire
 * @package    Phire
 * @link       https://github.com/phirecms/phirecms
 * @author     Nick Sagona, III <dev@nolainteractive.com>
 * @copyright  Copyright (c) 2009-2017 NOLA Interactive, LLC. (http://www.nolainteractive.com)
 * @license    http://www.phirecms.org/license     New BSD License
 * @version    3.0.0
 */
class Module extends AbstractModel
{

    /**
     * Get all modules
     *
     * @param  int    $limit
     * @param  int    $page
     * @param  string $sort
     * @return \Pop\Db\Record\Collection
     */
    public function getAll($limit = null, $page = null, $sort = null)
    {
        $order   = $this->getSortOrder($sort, $page);
        $options = ['order'  => $order];

        if (null !== $limit) {
            $page = ((null !== $page) && ((int)$page > 1)) ?
                ($page * $limit) - $limit : null;

            $options['offset'] = $page;
            $options['limit']  = $limit;
        }

        return Table\Modules::findAll($options);
    }

    /**
     * Determine if list of modules has pages
     *
     * @param  int $limit
     * @return boolean
     */
    public function hasPages($limit)
    {
        return (Table\Modules::findAll(null, Table\Modules::AS_ARRAY)->count() > $limit);
    }

    /**
     * Get count of modules
     *
     * @return int
     */
    public function getCount()
    {
        return Table\Modules::findAll(null, Table\Modules::AS_ARRAY)->count();
    }

}