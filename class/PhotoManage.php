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
		$this->aStoreForlder = $this->application()->fileSystem()->findFolder('/data/public/album');
		$arrPhotos = array();
		foreach( $this->modelPhoto->childIterator() as $aModelPhoto)
		{
			$aModelPhoto['imgUrl'] = $this->aStoreForlder->findFile($aModelPhoto['file'])->httpUrl();
			array_push($arrPhotos, $aModelPhoto);
		}
		$this->viewPhotoManage->variables()->set('arrPhotos',$arrPhotos) ;
    }
    
    public function process() {
		//必须登录,不登录不让玩
		$this->requireLogined('请先登录') ;
		
    	//是否有目标相册的所有权
    	//因为init部分根据用户的sessionID来确定显示什么图片给页面,所以这里无所谓是否判断用户权限
//		if( $this->nUid != $this->modelAlbum['uid'] )
//		{
//			$this->permissionDenied('没有权限',array()) ;
//		}
		
    	if ($this->viewPhotoManage->isSubmit ( $this->aParams )) {
			if( $this->aParams->has('photoDelete') && count($this->aParams->get('photoDelete')) > 0){
				//删除操作
				foreach($this->aParams->get('photoDelete') as $key=>$sPid){
					$nPid = (int)$sPid;
					if(($sPhotoPath = $this->modelPhoto->findChildBy($nPid)->data('file')) && $this->modelPhoto->findChildBy($nPid)->delete()){
						$aPhoto = $this->aStoreForlder->findFile($sPhotoPath);
						if(!$aPhoto->delete()){
							$this->messageQueue()->create( Message::error, "相册修改失败" );
							return;
						}
					}else{
						$this->messageQueue()->create( Message::error, "相册修改失败" );
						return;
					}
				}
				$this->viewPhotoManage->hideForm();
				$this->messageQueue()->create( Message::success, "照片已被删除" );
				return;
			}else if($this->aParams->has('photoAlbumFace') && count($this->aParams->get('photoAlbumFace')) > 0){
				$this->createModel('album');
				$this->modelAlbum->load($this->aParams['aid'],'aid');
				
				//设置封面操作
				$nPid = $this->aParams->get('photoAlbumFace');
				$this->modelAlbum['coverPid'] = (int)$nPid[0];
				if($this->modelAlbum->save()){
					$this->viewPhotoManage->hideForm();
					$this->messageQueue()->create( Message::success, "设置相册封面完成" );
					return;
				}else{
					$this->messageQueue()->create( Message::error, "相册修改失败" );
					return;
				}
			}else{
				$this->messageQueue()->create( Message::error, "相册修改失败" );
				return;
			}
		}
		else {
			
		}
    }
}

?>
