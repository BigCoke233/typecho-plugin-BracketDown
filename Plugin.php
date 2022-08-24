<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * BracketDown：MD 语法拓展
 * 
 * @package BracketDown
 * @author Eltrac
 * @version 1.0.0
 * @link http://www.guhub.cn
 */

require_once('Parser.php');
 
class BracketDown_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Abstract_Contents')->contentEx = array('BracketDown_Plugin','parseContent');
		Typecho_Plugin::factory('Widget_Abstract_Contents')->excerptEx = array('BracketDown_Plugin','parseContent');
		Typecho_Plugin::factory('Widget_Archive')->header = array('BracketDown_Plugin','header');
        Typecho_Plugin::factory('Widget_Archive')->footer = array('BracketDown_Plugin','footer');
    }
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $ifDefaultStyle = new Typecho_Widget_Helper_Form_Element_Select('ifDefaultStyle', array(
			'0' => '是',
			'1' => '否'
		), "0", _t('是否使用默认样式'), _t('BracketDown 定义了许多新的组件，他们有一套默认的样式，没有这些样式则不会正常显示；如果你使用的主题兼容 BracketDown 插件并且为其重写了样式，你可以选择不使用默认样式来避免主题覆盖插件 css 时带来的不便。<br>
		<strong>但通常情况下，你只需要将该设置保持默认。</strong>'));
        $form->addInput($ifDefaultStyle);

		$ifGitHubCard = new Typecho_Widget_Helper_Form_Element_Select('ifGitHubCard', array(
			'0' => '是',
			'1' => '否'
		), "1", _t('是否启用 GitHub 卡片'), _t('启用后支持 <code>[gh-card]</code> 语法，如果不使用这个语法建议关闭。'));
        $form->addInput($ifGitHubCard);
		
		$ifPolyfillDetails = new Typecho_Widget_Helper_Form_Element_Select('ifPolyfillDetails', array(
			'0' => '是',
			'1' => '否'
		), "0", _t('是否用垫片优化 details 标签兼容性'), _t('如果你使用 <code>[details]</code> 代码，插件会将其转换成 html 的 details 标签，这是一个较新的特性，不是所有的浏览器都能够兼容它。
		为了提高兼容性，你可以选择使用垫片，也就是引入一个额外的 js 文件，使得一些较老的浏览器也能正常渲染 details 标签。<br>
		虽然可能性很小，但这可能会导致其他问题，<strong>当有问题出现时，你可以尝试关闭该设置来排查原因。</strong>'));
        $form->addInput($ifPolyfillDetails);
    }
	
	/**
	 * 在头部输出内容
	 * 主要用于引入 css 文件
	 */
	public static function header()
    {
		$dir = Helper::options()->pluginUrl.'/BracketDown/assets';
		//引入默认样式
		if(Typecho_Widget::widget('Widget_Options')->plugin('BracketDown')->ifDefaultStyle=='0') {
			echo "<link rel=\"stylesheet\" href=\"{$dir}/default.css\">\n";
		}
		//定义 GitHub Card 样式
		if(Typecho_Widget::widget('Widget_Options')->plugin('BracketDown')->ifGitHubCard=='0') {
			echo '<meta name="gc:theme" content="medium">';
		}
    }
	
	/**
	 * 在页脚输出内容
	 * 主要用于引入 js 文件
	 */
	public static function footer()
    {
        $dir = Helper::options()->pluginUrl.'/BracketDown/assets';
		//引入 details 垫片
		if(Typecho_Widget::widget('Widget_Options')->plugin('BracketDown')->ifPolyfillDetails=='0') {
			echo "<script src=\"{$dir}/details-element-polyfill.js\"></script>";
		}
		//引入 GitHub Card
		if(Typecho_Widget::widget('Widget_Options')->plugin('BracketDown')->ifGitHubCard=='0') {
			echo "<script src=\"{$dir}/github-card.js\"></script>";
		}
    }

	/**
	 * 内容解析器入口
	 * 具体的解析代码在 Parser.php
	 */

	static public function parseContent($data, $widget, $last)
    {
        $text = empty($last) ? $data : $last;
        if ($widget instanceof Widget_Archive) {
			$text = BracketDown_Parser::linkToContent(
					BracketDown_Parser::underline(
					BracketDown_Parser::textColor(
					BracketDown_Parser::block(
					BracketDown_Parser::details(
					BracketDown_Parser::kbd(
					BracketDown_Parser::ruby($text
				)))))));
        }
        return $text;
    }

	/**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
	
	/**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
}
