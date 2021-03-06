<?php
/* For licensing terms, see /license.txt */

require_once 'Resource.class.php';

/**
 * A forum-category
 * @author Bart Mollet <bart.mollet@hogent.be>
 * @package chamilo.backup
 */
class ForumCategory extends Coursecopy\Resource
{
    /**
     * Create a new ForumCategory
     */
    public function __construct($obj)
    {
        parent::__construct($obj->cat_id, RESOURCE_FORUMCATEGORY);
        $this->obj = $obj;
    }

    /**
     * Show this resource
     */
    function show()
    {
        parent::show();
        echo $this->obj->cat_title;
    }
}
