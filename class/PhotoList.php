<?php
namespace oc\ext\album;

use jc\lang\Exception;

use oc\mvc\controller\Controller;               //控制器类
use oc\base\FrontFrame;                         //视图框架类
use oc\mvc\model\db\Model;                      //模型类
use jc\mvc\view\widget\Text;                    //文本组件类
use jc\mvc\view\widget\File;
use jc\mvc\view\widget\Select;
use jc\mvc\model\db\orm\PrototypeAssociationMap;    //模型关系类
use jc\message\Message;                       //消息类
use jc\verifier\Length;                         //长度校验类
use jc\verifier\NotEmpty;                       //非空校验类
use jc\verifier\FileSize;                       //非空校验类
use jc\verifier\FileExt;                       //非空校验类
use jc\mvc\view\DataExchanger;                 //数据交换类
use jc\auth\IdManager;                          //用户SESSION类
use jc\mvc\controller\Relocater;                //回调类
use jc\db\DB;                                   //数据库类

class PhotoList extends Controller {
    protected function init() {
        //创建默认视图
        $this->createView("PhotoList");

        $this->nAid = 0;
        //页面参数
        if($this->aParams->has('aid')){
        	$this->nAid = (int)$this->aParams->get('aid');
        	$this->viewPhotoList->variables()->set('nAidOfThisAlbum',$this->nAid) ;
		}else{
			$this->messageQueue()->create( Message::error,'没有选中相册' );
		}
		
		$this->nUid = 0;
    	//如果已经登录,就把当前的uid录入到uid字段
		if( IdManager::fromSession()->currentId() ){
			$this->nUid = IdManager::fromSession()->currentId()->userId() ;
		}
		
        //model
        $this->createModel('photo',array('owner'),true);
        $this->viewPhotoList->setModel($this->modelPhoto);
        $this->modelPhoto->criteria()->setLimit(-1);
//        $this->modelPhoto->criteria()->order('createTime',false);
		$this->modelPhoto->load(array($this->nUid, $this->nAid ),array('uid' , 'aid'));   //15  通过session得到用户uid
//		$this->modelPhoto->printStruct();

		$aStoreForlder = $this->application()->fileSystem()->findFolder('/data/public/album');
//		$this->viewPhotoList->variables()->set('aStoreForlder',$aStoreForlder) ;
		
        $arrPhotos = array();
    	foreach( $this->modelPhoto->childIterator() as $aModelPhoto)
		{
			$aModelPhoto['file'] = $aStoreForlder->findFile($aModelPhoto['file'])->httpUrl();
			array_push($arrPhotos, $aModelPhoto);
		}
		$this->viewPhotoList->variables()->set('arrPhotos',$arrPhotos) ;
//		var_dump($arrPhotos);
    }
    
    public function process() {
    	//必须登录,不登录不让玩
		$this->requireLogined() ;
		
		//是否有目标相册的所有权
		$bManageAccess = false;
		$this->createModel('album',array(), true);
//		$this->modelAlbum->criteria()->order('createTime',false);
		$this->modelAlbum->load();
		$aTargetAlbumModel = $this->modelAlbum->findChildBy($this->nAid);
		if( $this->nUid == $aTargetAlbumModel['uid'] )
		{
			$bManageAccess = true;
		}
		$this->viewPhotoList->variables()->set('bManageAccess',$bManageAccess) ;
    }
    
public function createFrame()
    {
    	$aFrame = new FrontFrame();
    	
    	$aFrame->mainView()->variables()->set('sTitle','相册') ;
    	
    	return $aFrame ;
    }
}

?>
