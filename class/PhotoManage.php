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

class PhotoManage extends Controller {
    protected function init() {
        //创建默认视图
        $this->createFormView("PhotoManage");

		//相册ID
		$this->nAid = 0;
		if($this->aParams->has('aid')){
			$this->nAid = (int)$this->aParams->get('aid');
			$this->viewPhotoManage->variables()->set('nAidOfThisAlbum',$this->nAid) ;
		}else{
			throw new Exception('正在执行管理相册的操作,却没有选中的相册');
		}
		
		//如果已经登录,就把当前的uid录入到uid字段
		$this->nUid = 0;
		if( IdManager::fromSession()->currentId() ){
			$this->nUid = IdManager::fromSession()->currentId()->userId() ;
		}
		
        //当前用户选定的相册下都有哪些图片
		$this->createModel('photo',array('owner'),true);
		$this->viewPhotoManage->setModel($this->modelPhoto);
		$this->modelPhoto->setLimit(-1);
		$this->modelPhoto->load(array($this->nUid, $this->nAid ),array('uid' , 'aid'));
		//取得图片信息,尤其是路径
		$aStoreForlder = $this->application()->fileSystem()->findFolder('/data/public/album');
		$arrPhotos = array();
		foreach( $this->modelPhoto->childIterator() as $aModelPhoto)
		{
			$aModelPhoto['file'] = $aStoreForlder->findFile($aModelPhoto['file'])->httpUrl();
			array_push($arrPhotos, $aModelPhoto);
		}
		$this->viewPhotoManage->variables()->set('arrPhotos',$arrPhotos) ;
		
    }
    
    public function process() {
    	//必须登录,不登录不让玩
		$this->requireLogined() ;
		
		//是否有目标相册的所有权
		$bManageAccess = false;
		$this->createModel('album',array(), true);
		$this->modelAlbum->load();
		$aTargetAlbumModel = $this->modelAlbum->findChildBy($this->nAid);
		if( $this->nUid == $aTargetAlbumModel['uid'] )
		{
			$bManageAccess = true;
		}
		$this->viewPhotoManage->variables()->set('bManageAccess',$bManageAccess) ;
		
		$this->viewPhotoManage->exchangeData ( DataExchanger::MODEL_TO_WIDGET );
    }
}

?>
