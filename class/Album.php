<?php
namespace oc\ext\album ;

use jc\mvc\model\db\orm\PrototypeAssociationMap;
use oc\ext\Extension;

class Album extends Extension
{
	public function load()
	{
		// 定义ORM
        $this->defineOrm(PrototypeAssociationMap::singleton()) ;
	}
	
	
	/**
	 * 
	 * Enter description here ...
	 * @param PrototypeAssociationMap $aAssocMap
	 */
	public function defineOrm(PrototypeAssociationMap $aAssocMap)
	{
	}
	fdsfsd
}

?><? ?>
