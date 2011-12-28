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
    	
		$this->nUid = 0;
    	//如果已经登录,就把当前的uid录入到uid字段
		if( IdManager::fromSession()->currentId() ){
			$this->nUid = IdManager::fromSession()->currentId()->userId() ;
		}
		
		//如果是针对某个相册的添加图片请求
		$this->nAid = 0;
		if($this->aParams->has('aid')){
			$this->nAid = $this->aParams->get('aid');
		}

        //创建默认视图
		$this->createFormView("AddPhoto");
        //model
		$this->createModel('photo');
		$this->createModel('album',array(),true);
		$this->viewAddPhoto->setModel($this->modelPhoto);
		$this->modelAlbum->load(array($this->nUid),'uid');
//		$this->modelAlbum->printStruct();
		$arrOptions = array(array('请选择相册...', '' , true));
		foreach( $this->modelAlbum->childIterator() as $aModelAlbum)
		{
			$arrAOption = array($aModelAlbum->title,$aModelAlbum->aid,false);
			array_push($arrOptions, $arrAOption);
		}
        
		$photoName = new Text('photoname','照片标题','',TEXT::single);
		$this->viewAddPhoto->addWidget ( $photoName ,'title')->dataVerifiers ()->add ( NotEmpty::singleton (), "照片标题不能为空" );
		
		$photoalbum = new Select ( 'photoalbum', '所属相册' );
		$photoalbum->addOptionByArray($arrOptions);
		//如果是针对某个相册的添加图片请求, 自动帮助用户选择对应的相册
		if($this->nAid > 0){
			$photoalbum->setValue($this->nAid);
		}
		$this->viewAddPhoto->addWidget( $photoalbum , 'aid')->dataVerifiers ()->add ( NotEmpty::singleton (), "所属相册不能为空" );
		
		$photoDescription = new Text('photodescription','照片描述','',TEXT::multiple);
		$this->viewAddPhoto->addWidget ( $photoDescription ,'discription');
        
		$uploadForlder = $this->application()->fileSystem()->findFolder('/data/public/album');
		$this->photoupdate = new File ( 'photoupdate','图片上传',$uploadForlder );
//		$photoupdate->addVerifier(FileSize::flyweight(array(200,200000)));
//		$photoupdate->addVerifier(FileExt::flyweight(array(array('jpg','png','bmp'),true)));
		$this->viewAddPhoto->addWidget ( $this->photoupdate ,'file')->dataVerifiers ()->add ( NotEmpty::singleton (), "照片不能为空" );
    }
    
    public function process() {
    	//必须登录,不登录不让玩
		$this->requireLogined('请先登录') ;
    	
    	if ($this->viewAddPhoto->isSubmit ( $this->aParams )) {
			do {
				$this->viewAddPhoto->loadWidgets ( $this->aParams );
				if (! $this->viewAddPhoto->verifyWidgets ()) {
					$this->photoupdate->setValue(null);
					break ;
				}
				
				//是否有目标相册的所有权
				$aSelectAlbumModel = $this->modelAlbum->findChildBy($this->aParams->get('photoalbum'));
				if( $this->nUid != $aSelectAlbumModel['uid'] )
				{
					$this->permissionDenied('没有权限',array()) ;
				}
				
				$this->viewAddPhoto->exchangeData ( DataExchanger::WIDGET_TO_MODEL );
				try{
					
					//如果是在新建照片,就带上一个创建时间
					if(!$this->aParams->has('pid')){
						$this->modelPhoto->createTime = time() ;
					}
					
					//如果已经登录,就把当前的uid录入到uid字段,但事实上,编辑表单是需要权限的,所以在权限做好以后应该省略判断
					if( IdManager::fromSession()->currentId() && $uidFromSession = IdManager::fromSession()->currentId()->userId() ){
						$this->modelPhoto->uid = $uidFromSession;
					}
					
					//记录文件大小
					if($this->aParams->has('photoupdate')){
						if(($aFile = $this->viewAddPhoto->widget('photoupdate')->value()) != null){
							$this->modelPhoto->bytes = $aFile->length();
						}else{
							$this->messageQueue()->create( Message::error, "照片提交失败" );
							return ;
						}
					}
					
					if($this->modelPhoto->save()){
						
						//更新相册总大小
						$aSelectAlbumModel->bytes = $aSelectAlbumModel->bytes + $this->modelPhoto->bytes;
						if(!$aSelectAlbumModel->save()){
							throw new Exception('更新相册总大小失败!');
						}
						
						$this->viewAddPhoto->hideForm();
						$this->messageQueue()->create( Message::success, "照片提交完成" );
					}else{
						$this->messageQueue()->create( Message::error, "照片提交失败" );
					}
				}catch (Exception $e){
					$this->messageQueue()->create( Message::error, "照片提交失败" );
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
