<?php
namespace oc\ext\album;

use jc\db\sql\Order;
use jc\mvc\view\View;
use jc\lang\Exception;
use oc\mvc\controller\Controller;               //控制器类
use oc\ext\album\FrontFrame;                         //视图框架类
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

class AlbumList extends Controller {
    protected function init() {
        //创建默认视图
        $this->createView("AlbumList");
    }
    
    public function process() {
    	//识别用户
   		$this->nUid = 0;
    	$this->bManageAccess = false;
    	if($this->aParams->has('uid')){   //查看指定用户的相册
    		$this->nUid = $this->aParams->get('uid');
    	}else if( IdManager::fromSession()->currentId() ){  //如果是查看自己的相册,就把当前session中uid录入到uid字段
			$this->nUid = IdManager::fromSession()->currentId()->userId() ;
			$this->bManageAccess = true;
		}else{
			$this->messageQueue()->create(Message::error, '没有指定显示看谁的相册,或者未登录');
			return;
		}
		
    	//加载model
        $this->createModel('album',array('owner','cover','photos'),true);
        $this->viewAlbumList->setModel($this->modelAlbum);
        
        $this->modelAlbum->criteria()->setLimit(-1);
		$this->modelAlbum->load($this->nUid,'uid');
//    	$this->modelAlbum->printStruct();
		$this->viewAlbumList->variables()->set('bManageAccess',$this->bManageAccess) ;
    }
    
	public function createFrame()
    {
    	return new FrontFrame();
    }
}
?>
