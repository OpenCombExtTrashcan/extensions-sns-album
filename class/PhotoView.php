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

class PhotoView extends Controller {
	protected function init() {
        //创建默认视图
		$this->createView("PhotoView");

		$nPid = 0;
        //页面参数
		if($this->aParams->has('pid')){
			$nPid = (int)$this->aParams->get('pid');
		}else{
			$this->messageQueue()->create( Message::error,'没有选中照片' );
		}
		
		$nUid = 0;
		//如果已经登录,就把当前的uid录入到uid字段
		if( IdManager::fromSession()->currentId() ){
			$nUid = IdManager::fromSession()->currentId()->userId() ;
		}
		
        //model
		$this->createModel('photo',array('owner'));
		$this->viewPhotoView->setModel($this->modelPhoto);
		$this->modelPhoto->load(array($nUid , $nPid ),array('uid' , 'pid'));   //15  通过session得到用户uid
//		$this->modelPhoto->printStruct();

		$aStoreForlder = $this->application()->fileSystem()->findFolder('/data/public/album');
		
		$this->modelPhoto['file'] = $aStoreForlder->findFile($this->modelPhoto['file'])->httpUrl();
		$this->viewPhotoView->variables()->set('aPhoto',$this->modelPhoto) ;
	}
    
	public function process() {
		//必须登录,不登录不让玩
		$this->requireLogined() ;
	}
}

?>