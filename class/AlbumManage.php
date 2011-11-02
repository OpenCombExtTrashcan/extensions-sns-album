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

class AlbumManage extends Controller {
    protected function init() {
    	$this->createView('AlbumManage');
    	
		$aEditAlbum = new EditAlbum($this->aParams);
		$aPhotoManage = new PhotoManage($this->aParams);
		$this->add($aEditAlbum);
		$this->add($aPhotoManage);
    }
    
    public function process() {
    	//必须登录,不登录不让玩
		$this->requireLogined('请先登录') ;
		
		//是否有目标相册的所有权
		$this->createModel('album');
//		$this->modelAlbum->criteria()->order('createTime',false);
		$this->modelAlbum->load($this->aParams->get('aid'),'aid');
		
    	//是否有目标相册的所有权
    	if( IdManager::fromSession()->currentId() && $uidFromSession = IdManager::fromSession()->currentId()->userId() ){
			$this->nUid = $uidFromSession;
		}
		if( $this->nUid != $this->modelAlbum['uid'] )
		{
			$this->permissionDenied('没有权限',array()) ;
		}
    }
public function createFrame()
    {
    	$aFrame = new FrontFrame();
    	
    	$aFrame->mainView()->variables()->set('sTitle','相册') ;
    	
    	return $aFrame ;
    }
}

?>
