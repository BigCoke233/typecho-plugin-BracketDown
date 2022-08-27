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
     * 解析站内文章卡片
     * credit youranreus/G
     * edited
     */
    static public function postCard($text)
    {
        if (preg_match_all("/\[art\](.*?)\[\/art\]/s", $text, $matches)){
                
                $i = 0;
                foreach($matches[1] as $id) {
                    $db = Typecho_Db::get();
                    $result = $db->fetchAll($db->select()->from('table.contents')
                        ->where('status = ?', 'publish')
                        ->where('type = ?', 'post')
                        ->where('cid = ?', $id)
                    );

                    if($result){
                        $val = Typecho_Widget::widget('Widget_Abstract_Contents')->push($result[0]);
                        $excerpt = mb_substr($val['text'], 0, 100, 'utf-8');
                        $text = str_replace(
                            $matches[0][$i],
                            '<div class="bracketdown-post">
                                <h4 class="bracketdown-post-title"><a href="'.$val['permalink'].'">'.$result[0]['title'].'</a></h4>
                                <p class="bracketdown-post-excerpt">'.$excerpt.'...</p>
                                <p class="bracketdown-post-meta">
                                    <span>'.date('Y-m-d', $val['created']).'</span>
                                    <a href="'.$val['permalink'].'">阅读全文</a>
                                </p>
                            </div>', 
                        $text);
                    }else{
                        $text = str_replace(
                            $matches[0][$i],
                            '<div class="bracketdown-post"><p>文章 cid 错误，获取不到信息。</p></div>'
                        );
                    }

                    $i++;

            }

            return $text;
        }else{
            return $text;
        }

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
        if(Typecho_Widget::widget('Widget_Options')->plugin('BracketDown')->ifParseLink=='0') {
            //若文章中有直接写入的链接
            if(preg_match_all('/<a(.*?)href="(.*?)"(.*?)>(.*?)<\/a>/is',$text,$matches)){

                $i=0;
                foreach ($matches[0] as $child){
                    if ($matches[2][$i] != $matches[4][$i]){
                        $i+=1;
                        continue;
                    }

                    $strip_child = strip_tags($child);
                    $text = BracketDown_Parser::bilibili(
                            BracketDown_Parser::github(
                                $text, 
                                preg_quote($child,'/'),
                                $strip_child
                            ), 
                            preg_quote($child,'/'),
                            $strip_child
                    );
                    $i+=1;
                }
            }
        }

        return $text;
    }

    /**
     * 解析 github 链接
     */
    static public function github($text, $replace, $url) 
    {
        if (preg_match("/https?:\/\/github.com\/(.*?)\/(.*?)/is",$url,$matches)){

            if (preg_match("/https?:\/\/github.com\/blog\/(.*?)/is",$url,$matches)){
                return $text;
            }
            if (preg_match("/https?:\/\/github.com\/(.*?)\/(.*?)\/(.*?)\//is",$url,$matches)){
                return $text;
            }
                
            //链接可以被解析
            $data = str_replace('https://github.com/','', $url);
            $text = preg_replace(
                '/'.$replace.'/i',
                '<div class="github-card" data-github="'.$data.'">Loading...</div>',
                $text
            );

        } else {
            return $text;
        }

        return $text;
    }

    /**
     * 创建 bilibili 嵌入代码
     */
    static public function bilibili($text, $replace, $url) 
    {
        if (preg_match("/https?:\/\/bilibili.com\/video\/(.*?)/is",$url,$matches)){
            $text = preg_replace(
                '/'.$replace.'/i',
                '<iframe src="'.BracketDown_Parser::bilibiliURL($url).'" class="bilibili-video-player" scrolling="no" border="0" frameborder="no" framespacing="0" allowfullscreen="true"> </iframe>',
                $text
            );
            return $text;
        }
    }

    /**
     * 解析 bilibili 链接
     */
    static public function bilibiliURL($url) 
    {
        $quality_request = "&as_wide=1&high_quality=1";
        if(preg_match("/https?:\/\/(m.|www.|)bilibili.(com|tv)\/video\/(a|b)v([A-Za-z0-9]+)(\/?.*?&p=|\/?\?p=)?(\d+)?/i", $url, $matches)) {
            $vid = (is_numeric($matches[4]) ? 'aid='.$matches[4] : 'bvid='.$matches[4]) . (empty($matches[6]) ? '' : '&page='.intval($matches[6]));
            $iframe = 'https://player.bilibili.com/player.html?'.$vid.$quality_request;

            return $iframe;

        } else if(preg_match("/https?:\/\/(www.|)(acg|b23).tv\/(a|b)v([A-Za-z0-9]+)(\/?.*?&p=|\/?\?p=)?(\d+)?/i", $url, $matches)) {
            $vid = (is_numeric($matches[4]) ? 'aid='.$matches[4] : 'bvid='.$matches[4]) . (empty($matches[6]) ? '' : '&page='.intval($matches[6]));
            $iframe = 'https://player.bilibili.com/player.html?'.$vid.$quality_request;

            return $iframe;

        } else {
            return 0;
        }
    }
}