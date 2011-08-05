<?php
namespace oc\ext\album ;

use jc\mvc\model\db\orm\Association;

use jc\mvc\model\db\orm\PrototypeAssociationMap;
use oc\ext\Extension;

class Album extends Extension
{
	public function load()
	{
		// 定义ORM
		$this->defineOrm(PrototypeAssociationMap::singleton()) ;
		
		$this->application()->accessRouter()->addController("oc\\ext\\album\\AlbumList",'albumList');
		
		$this->application()->accessRouter()->addController("oc\\ext\\album\\PhotoList",'photoList');
		
		$this->application()->accessRouter()->addController("oc\\ext\\album\\PhotoView",'photoView');
        
		$this->application()->accessRouter()->addController("oc\\ext\\album\\AddPhoto",'addPhoto');
        
		$this->application()->accessRouter()->addController("oc\\ext\\album\\AddAlbum",'addAlbum');
		
		//相册管理
		$this->application()->accessRouter()->addController("oc\\ext\\album\\AlbumManage",'albumManage');
		$this->application()->accessRouter()->addController("oc\\ext\\album\\EditAlbum",'editAlbum');
		$this->application()->accessRouter()->addController("oc\\ext\\album\\PhotoManage",'photoManage');
        
		$this->application()->accessRouter()->addController("oc\\ext\\album\\EditPhoto",'editPhoto');
	}
	
	/**
	 * 
	 * Enter description here ...
	 * @param PrototypeAssociationMap $aAssocMap
	 */
	public function defineOrm(PrototypeAssociationMap $aAssocMap)
	{
        $aAssocMap->addOrm(
                array(
                    'keys' => 'aid', //主键
                    'table' => 'album', //模型名称     
//                	'name'
//					'columns'
//					'deviceP'
                    'hasMany' => array(
        				array(
        					'prop' => 'photos', //属性名
        					'fromk' => 'aid', //主键
        					'tok' => 'aid', //外键
                            'model' => 'photo'  //模型名称
        				),
                    ),
                    'belongsTo' => array(
                    	array(
        					'prop' => 'owner', //属性名
        					'fromk' => 'uid', //主键
        					'tok' => 'uid', //外键
                            'model' => 'coreuser:user'  //模型名称
        				)
                    )
                )
        );
        
        $aAssocMap->addOrm(
        		array(
        			'keys' => 'pid',
        			'table' => 'photo',
        			'belongsTo' => array(
                        array(
                            'prop' => 'album', //属性名
                            'fromk' => 'aid', //主键
                            'tok' => 'aid', //从外键
                            'model' => 'album', //模型名称
                        ),
                        array(
                            'prop' => 'owner', //属性名
                            'fromk' => 'uid', //主键
                            'tok' => 'uid', //从外键
                            'model' => 'coreuser:user', //模型名称
                        )
					)
        		)
        );
        
        //扩展user模型的数据
        $aPrototypeUser = $aAssocMap->modelPrototype('coreuser:user');
        $aPrototypeAlbum = $aAssocMap->modelPrototype('album:album');
        $aPrototypePhoto = $aAssocMap->modelPrototype('album:photo');
        
        $aPrototypeUser->addAssociation(new Association(Association::hasMany,'albums',$aPrototypeUser,$aPrototypeAlbum,'uid','uid')) ;
        $aPrototypeUser->addAssociation(new Association(Association::hasMany,'photos',$aPrototypeUser,$aPrototypePhoto,'uid','uid')) ;
	}
}

?>
