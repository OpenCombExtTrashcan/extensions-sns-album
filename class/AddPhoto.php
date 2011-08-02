<?php

namespace oc\ext\album;

use oc\mvc\controller\Controller;               //控制器类
use oc\base\FrontFrame;                         //视图框架类
use oc\mvc\model\db\Model;                      //模型类
use jc\mvc\view\widget\Text;                    //文本组件类
use jc\mvc\model\db\orm\PrototypeAssociationMap;    //模型关系类
use jc\message\Message;                        //消息类
use jc\verifier\Length;                         //长度校验类
use jc\verifier\NotEmpty;                       //非空校验类
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
        $this->createView("AddPhoto", "addPhoto.html", true);
        
//        $this->createModel('photo',array('')) ;
        
        //设定模型
//        $this->aUserModel = Model::fromFragment('user', array('info')) ;
    }
    
    public function process() {
    	if( $this->viewAddPhoto->isSubmit( $this->aParams ) )		 
		{
			do{
//				// 加载 视图窗体的数据
//				$this->viewLogin->loadWidgets( $this->aParams ) ;
//				            	
//				// 校验 视图窗体的数据
//				if( !$this->viewLogin->verifyWidgets() )
//				{
//					break ;
//				}
//					
//				if( !$this->aUserModel->load($this->aParams["username"],"username") )
//				{
//					$this->viewLogin->createMessage( Message::failed, "用户名不存在:%s。", $this->aParams["username"] ) ;
//					break ;
//				}
//	
//				if( $this->aUserModel['password'] != $this->aParams["password"] )
//				{
//					$this->viewLogin->createMessage( Message::failed, "密码错误。" ) ;
//					break ;
//				}
//	            		
//				// IdManager::fromSession()->clear() ;
//				IdManager::fromSession()->addId($aId) ;
//				
//				$this->viewLogin->createMessage( Message::success, "登录成功。" ) ;
//				$this->viewLogin->hideForm() ;
				
			} while(0) ; 
		}
    }
}
//
//        //为视图创建、添加textarea文本组件(Text::multiple 复文本) （Text::single 标准文本）
//        $this->viewadd->addWidget(new Text("text", "内容", "", Text::multiple), 'text')
//                ->dataVerifiers()
//                ->add(Length::flyweight(array(0, 140)), "长度不能超过140个字")
//                ->add(NotEmpty::singleton(), "必须输入");
//
//        
//        // 为视图创建、添加images文本组件
//        $this->viewadd->addWidget( new Text("image","图片"), 'image' );
//
//        // 为视图创建、添加videos文本组件
//        $this->viewadd->addWidget( new Text("video","视频"), 'video' );
//
//        // 为视图创建、添加musics文本组件
//        $this->viewadd->addWidget( new Text("music","音乐"), 'music' );
//         

?>
