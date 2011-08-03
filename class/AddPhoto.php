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

class AddPhoto extends Controller {
    protected function init() {
        //是否登陆
//		if(!IdManager::fromSession()->currentId())
//		{
//		    echo "请先登陆";
//		}

        //创建默认视图
		$this->createFormView("AddPhoto");
        //model
		$this->createModel('photo');
		$this->createModel('album',null,true);
		$this->modelAlbum->setLimit(-1);
		$this->viewAddPhoto->setModel($this->modelPhoto);
        
		$photoName = new Text('photoname','照片标题','',TEXT::single);
		$this->viewAddPhoto->addWidget ( $photoName ,'title')->dataVerifiers ()->add ( NotEmpty::singleton() );
		
		$photoalbum = new Select ( 'photoalbum', '所属相册' );
		$photoalbum->addOption ( '请选择相册...', 0 , true );
		$photoalbum->addOption ( "5434", 1 );
		$photoalbum->addOption ( "fdasf", 2 );
		$this->viewAddPhoto->addWidget( $photoalbum , 'aid')->dataVerifiers ()->add ( NotEmpty::singleton (), "所属相册" );
		
		$photoDescription = new Text('photodescription','照片描述','',TEXT::multiple);
		$this->viewAddPhoto->addWidget ( $photoDescription ,'description');
        
		$uploadForlder = $this->application()->fileSystem()->findFolder('/data/public/album');
		$photoupdate = new File ( 'photoupdate','图片上传',$uploadForlder );
//		$photoupdate->addVerifier(FileSize::flyweight(array(200,200000)));
//		$photoupdate->addVerifier(FileExt::flyweight(array(array('jpg','png','bmp'),true)));
		$this->viewAddPhoto->addWidget ( $photoupdate ,'file');
    }
    
    public function process() {
    	if ($this->viewAddPhoto->isSubmit ( $this->aParams )) {
			do {
				$this->viewAddPhoto->loadWidgets ( $this->aParams );
				if (! $this->viewAddPhoto->verifyWidgets ()) {
//					$aMsgQueue = $this->messageQueue ();
					break ;
				}
				
				$this->viewAddPhoto->exchangeData ( DataExchanger::WIDGET_TO_MODEL );
				try{
					if($this->modelPhoto->save()){
						$this->viewAddPhoto->messageQueue()->create( Message::success, "表单提交完成" );
					}else{
						$this->viewAddPhoto->messageQueue()->create( Message::error, "表单提交失败" );
					}
				}catch (Exception $e){
					$this->viewAddPhoto->messageQueue()->create( Message::error, "表单提交失败" );
				}
				
			} while ( 0 );
		}
		else {
			
			
		}
    }
}

?>
