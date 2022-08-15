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
		
		$ifGrid = new Typecho_Widget_Helper_Form_Element_Select('ifGrid', array(
			'0' => '是',
			'1' => '否'
		), "0", _t('是否引入 grid.css'), _t('通常情况下，引入 grid.css 才会使插件的网格语法生效，也就是 <code>[row]</code> 和 <code>[col]</code>。
		插件引入的是 bootstrap 的 bootstrap-grid.css，如果你使用的主题也引入了这个 css，或者说与这个 css 使用相同的类名并定义了相似的样式，就可以不额外引入 grid.css。
		如果你不理解，<strong>在没有问题出现的情况下，请保持该设置项默认开启。</strong>'));
        $form->addInput($ifGrid);
		
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
		//引入网格样式
		if(Typecho_Widget::widget('Widget_Options')->plugin('BracketDown')->ifGrid=='0') {
			echo "<link rel=\"stylesheet\" href=\"{$dir}/bootstrap-grid.css\">\n";
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
    }
	
	/* ----- */
	
	/**
	 * 内容解析器
	 */
	static public function parseContent($data, $widget, $last)
    {
        $text = empty($last) ? $data : $last;
        if ($widget instanceof Widget_Archive) {
			$text = BracketDown_Plugin::parseUnderline(BracketDown_Plugin::parseGrid(BracketDown_Plugin::parseTextColor(BracketDown_Plugin::parseBlock(BracketDown_Plugin::parseDetails(BracketDown_Plugin::parseKbd(BracketDown_Plugin::parseRuby($text)))))));
        }
        return $text;
    }
	
	/**
	 *  解析注音
	 */
    static public function parseRuby($text)
    {
		$reg = '/\{\{(.*?):(.*?)\}\}/s';
        $rp = '<ruby>${1}<rp>(</rp><rt>${2}</rt><rp>)</rp></ruby>';
        $text = preg_replace($reg,$rp,$text);
		
		return $text;
	}
	
	/**
	 * 解析键盘按键
	 */
    static public function parseKbd($text) 
    {
		$text = preg_replace('/\[\[(.*?)\]\]/s','<kbd>${1}</kbd>',$text);
		return $text;
    }
	
	/**
	 * 解析 <details>
	 */
    static public function parseDetails($text) 
    {
        $text = preg_replace(
			'/\[details sum="(.*?)"(.*?)\](.*?)\[\/details\]/s',
			'<details class="bracketdown"${2}><summary>${1}</summary><div class="bracketdown-details-content">${3}</div></details>',
		$text);
        $text = preg_replace('/\[details(.*?)\](.*?)\[summary\](.*?)\[\/summary\](.*?)\[\/details\]/s','<details class="bracketdown"${1}><summary>${3}</summary><div class="bracketdown-details-content">${4}</div></details>',$text);
        return $text;
    }
	
	/**
	 * 解析文字块
	 */
    static public function parseBlock($text) 
    {
        $text = preg_replace(
			'/\[block(.*?)\](.*?)\[\/block\]/s',
			'<div class="bracketdown-block"${1}>${2}</div>',
		$text);
        return $text;
    }
	
	/**
	 *  解析 Text-Color
	 */
    static public function parseTextColor($text)
    {
		$text = preg_replace(
			'/\&\{(.*?)\|(.*?)\|(.*?)\}/s',
			'<span style="color:${2};background:${3}">${1}</span>'
		,$text);
		$text = preg_replace(
			'/\&\{(.*?)\|(.*?)\}/s',
			'<span style="color:${2}">${1}</span>'
		,$text);
		$text = preg_replace(
			'/\%\{(.*?)\|(.*?)\}/s',
			'<span style="background:${2}">${1}</span>'
		,$text);
		return $text;
	}
	
	/**
	 *  解析网格
	 */
    static public function parseGrid($text)
    {
		$text = preg_replace(
			'/\[row(.*?)\](.*?)\[\/row\]/s',
			'<div class="row"${1}>${2}</div>'
		,$text);
		$text = preg_replace(
			'/\[col grid=\"(.*?)\"(.*?)\](.*?)\[\/col\]/s',
			'<div class="col-${1}"${2}>${3}</div>'
		,$text);
		//quick method
		$text = preg_replace(
			'/\[(.*?)half(.*?)\](.*?)\[\/half\]/s',
			'<div class="col-${1}6"${2}>${3}</div>'
		,$text);
		
		return $text;
	}
	
	/**
	 *  解析下划线
	 */
    static public function parseUnderline($text)
    {
		$text = preg_replace(
			'/\?(.*?)\?/s',
			'<span class="bracketdown-underline">${1}</span>'
		,$text);
		
		return $text;
	}
}
