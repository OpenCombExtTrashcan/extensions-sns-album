<?php
namespace oc\ext\album;

use jc\mvc\controller\WebpageFrame;
use jc\mvc\view\View;

class FrontFrame extends WebpageFrame
{
	public function __construct()
	{
		parent::__construct() ;
		$this->addFrameView(
			new View('frameView',"album:FrontFrame.template.html")
		) ;
	}
}
?>