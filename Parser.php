<?php
/**
 * Parser.php
 * 内容解析器
 */

Class BracketDown_Parser {
    /**
	 *  解析注音
	 */
    static public function ruby($text)
    {
		$reg = '/\{\{(.*?):(.*?)\}\}/s';
        $rp = '<ruby>${1}<rp>(</rp><rt>${2}</rt><rp>)</rp></ruby>';
        $text = preg_replace($reg,$rp,$text);
		
		return $text;
	}
	
	/**
	 * 解析键盘按键
	 */
    static public function kbd($text) 
    {
		$text = preg_replace('/\[\[(.*?)\]\]/s','<kbd>${1}</kbd>',$text);
		return $text;
    }
	
	/**
	 * 解析 <details>
	 */
    static public function details($text) 
    {
        $text = preg_replace(
			'/\[details sum="(.*?)"\](.*?)\[\/details\]/s',
			'<details class="bracketdown"><summary>${1}</summary><div class="bracketdown-details-content">${2}</div></details>',
		$text);
        $text = preg_replace('/\[details\](.*?)\[summary\](.*?)\[\/summary\](.*?)\[\/details\]/s','<details class="bracketdown"><summary>${2}</summary><div class="bracketdown-details-content">${3}</div></details>',$text);
        return $text;
    }
	
	/**
	 * 解析文字块
	 */
    static public function block($text) 
    {
        $text = preg_replace(
			'/\[block\](.*?)\[\/block\]/s',
			'<div class="bracketdown-block">${1}</div>',
		$text);
        return $text;
    }
	
	/**
	 *  解析 Text-Color
	 */
    static public function textColor($text)
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
	 *  解析下划线
	 */
    static public function underline($text)
    {
		$text = preg_replace(
			'/\?(.*?)\?/s',
			'<span class="bracketdown-underline">${1}</span>'
		,$text);
		
		return $text;
	}

    /**
     * 解析文章内直接写入的链接
     * 将其变为更容易阅读的形式
     * 
     * github()、bilibili() 等方法的入口
     * 
     * Credit https://github.com/ShangJixin/Typecho-Plugin-superLink/blob/main/JixinParser.php
     */
    static public function linkToContent($text) {
        //若文章中有直接写入的链接
        if(preg_match_all('/<a(.*?)href="(.*?)"(.*?)>(.*?)<\/a>/is',$text,$matches)){
            foreach ($matches[0] as $child){
                $strip_child = strip_tags($child);
                $text = BracketDown_Parser::github(
                            BracketDown_Parser::bilibili(
                                $text, 
                                preg_quote($child,'/'),
                                $strip_child
                            ), 
                            preg_quote($child,'/'),
                            $strip_child
                        );
            }
        }

        return $text;
    }

    /**
     * 解析 github 链接
     */
    static public function github($text, $replace, $url) {

        if (preg_match("/https?:\/\/github.com\/(.*?)\/(.*?)/is",$url,$matches)){
            if (preg_match("/https?:\/\/github.com\/blog\/(.*?)/is",$url,$matches)){
                return $text;
            }
            if (preg_match("/https?:\/\/github.com\/(.*?)\/(.*?)\/(.*?)\//is",$url,$matches)){
                return $text;
            }
                
            //链接可以被解析
            $data = str_replace('https://github.com/','', $url);
            $data = explode('/', $data);

            $text = preg_replace(
                '/'.$replace.'/i',
                '<div class="github-card" data-user="'.$data[0].'" data-repo="'.$data[1].'">Loading...</div>',
                $text
            );

        } else {
            return $text;
        }

        return $text;
    }

    /**
     * 解析 bilibili 链接
     */
    static public function bilibili($text, $replace, $url) {
        return $text;
    }
}