<?php
namespace oc\ext\album;

use jc\mvc\view\widget\CheckBtn;

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

class EditAlbum extends Controller {
    protected function init() {

		if(!$this->aParams->has('aid')){
			throw new Exception('编辑相册功能需要提供相册的主键作为参数');
		}
		
    	//创建默认视图
        $this->createFormView("EditAlbum");
        
        $albumname = new Text('albumname','相册标题','',TEXT::single);
		$this->viewEditAlbum->addWidget ( $albumname ,'title')->dataVerifiers ()->add ( NotEmpty::singleton() )->add ( Length::flyweight(array(0,100)));
		
		$albumdescription = new Text('albumdescription','相册描述','',TEXT::multiple);
		$this->viewEditAlbum->addWidget ( $albumdescription ,'description')->dataVerifiers ()->add ( Length::flyweight(array(0,255)));
		
		$albumedit = new CheckBtn('albumdelete','删除相册','albumdelete',CheckBtn::checkbox);
		$this->viewEditAlbum->addWidget ( $albumedit );
    	
    	//数据库中的数据
		$this->createModel('album');
		$this->modelAlbum->load($this->aParams['aid'],'aid');
		$this->viewEditAlbum->setModel($this->modelAlbum);
		if (!$this->viewEditAlbum->isSubmit ( $this->aParams )) {
			$this->viewEditAlbum->exchangeData( DataExchanger::MODEL_TO_WIDGET );
		}
    }
    
    public function process() {
    	//必须登录,不登录不让玩
		$this->requireLogined('请先登录') ;
		
    	//是否有目标相册的所有权
    	if( IdManager::fromSession()->currentId() && $uidFromSession = IdManager::fromSession()->currentId()->userId() ){
			$this->nUid = $uidFromSession;
		}
		if( $this->nUid != $this->modelAlbum['uid'] )
		{
			$this->permissionDenied('没有权限',array()) ;
		}
		
    	if ($this->viewEditAlbum->isSubmit ( $this->aParams )) {
			do {
				$this->viewEditAlbum->loadWidgets ( $this->aParams );
				if (! $this->viewEditAlbum->verifyWidgets ()) {
					break ;
				}
				
				$this->viewEditAlbum->exchangeData ( DataExchanger::WIDGET_TO_MODEL );
				try{
					if( $this->aParams->has('albumdelete') && $this->aParams->get('albumdelete') == 'albumdelete'){
						//如果相册内的图片数量>0 , 提示用户自己删除这些照片以后再回来删除相册
						$this->createModel('photo' ,array() , true);
//						$this->modelAlbum->criteria()->order('createTime',false);
						$this->modelAlbum->load($this->aParams['aid'],'aid');
						if( $this->modelPhoto->childrenCount() > 0 ){
							$this->messageQueue()->create( Message::error, "必须是空相册才能删除,请先处理相册内的照片" );
							break;
						}
						//删除操作
						if(!$this->modelAlbum->delete()){
//							$this->messageQueue()->create( Message::error, "删除相册失败" );
							throw new Exception('删除相册失败');
						}else{
							$this->viewEditAlbum->hideForm();
//							$this->messageQueue()->create( Message::success, "相册已被删除" );
							header('Location: /?c=album.albumList');
							break;
						}
					}else if($this->modelAlbum->save()){
						$this->viewEditAlbum->hideForm();
						$this->messageQueue()->create( Message::success, "相册修改完成" );
						break;
					}else{
						$this->messageQueue()->create( Message::error, "相册修改失败" );
						break;
					}
				}catch (Exception $e){
					$this->messageQueue()->create( Message::error, "相册修改失败" );
				}
			} while ( 0 );
		}
		else {
			
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
