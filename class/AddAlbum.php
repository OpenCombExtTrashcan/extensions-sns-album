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

class AddAlbum extends Controller {
    protected function init() {
        //是否登陆
//		if(!IdManager::fromSession()->currentId())
//		{
//		    echo "请先登陆";
//		}

        //创建默认视图
        $this->createFormView("AddAlbum");
        //model
        $this->createModel('album');
        $this->viewAddAlbum->setModel($this->modelAlbum);
        
        $albumname = new Text('albumname','相册标题','',TEXT::single);
		$this->viewAddAlbum->addWidget ( $albumname ,'title')->dataVerifiers ()->add ( NotEmpty::singleton() )->add ( Length::flyweight(array(0,100)));
		
		$albumdescription = new Text('albumdescription','相册描述','',TEXT::multiple);
		$this->viewAddAlbum->addWidget ( $albumdescription ,'description')->dataVerifiers ()->add ( Length::flyweight(array(0,255)));
		
    }
    
    public function process() {
    	if ($this->viewAddAlbum->isSubmit ( $this->aParams )) {
    		
			do {
				$this->viewAddAlbum->loadWidgets ( $this->aParams );
				if (! $this->viewAddAlbum->verifyWidgets ()) {
//					$aMsgQueue = $this->messageQueue ();
					break ;
				}
				
				$this->viewAddAlbum->exchangeData ( DataExchanger::WIDGET_TO_MODEL );
				try{
					//如果是在新建相册,就带上一个创建时间
					if(!$this->aParams->has('pid')){
						$this->modelAlbum->createTime = time() ;
					}
					
					//如果已经登录,就把当前的uid录入到uid字段,但事实上,编辑表单是需要权限的,所以在权限做好以后应该省略判断
					if( IdManager::fromSession()->currentId() && $uidFromSession = IdManager::fromSession()->currentId()->userId() ){
						$this->modelAlbum->uid = $uidFromSession;
					}
					
					if($this->modelAlbum->save()){
						$this->viewAddAlbum->hideForm();
						$this->messageQueue()->create( Message::success, "表单提交完成" );
					}else{
						$this->messageQueue()->create( Message::error, "表单提交失败" );
					}
				}catch (Exception $e){
					$this->messageQueue()->create( Message::error, "表单提交失败" );
				}
			} while ( 0 );
		}
		else {
			
		}
    }
}

?>
