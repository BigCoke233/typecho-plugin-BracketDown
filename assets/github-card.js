(function(){

    function handleError(status) {
        var error_html = '数据获取失败，错误信息：<br>'+status+
            '<button id="reload-'+$(card).attr('id')+'">重新加载</button>';
        return error_html;
    }

    function renderRepo(data) {
        var html = '<section class="github-card-content">'+
        '<h4 class="github-card-title">'+data['name']+'</h4>'+
            '<p class="github-card-des">'+data['description']+'</p>'+
            '<div class="github-card-footer"><ul class="github-card-meta">'+
                '<li>Star '+data['stargazers_count']+'</li>'+
                '<li>Fork '+data['forks']+'</li>'+
            '</ul>'+
            '<a href="'+data['html_url']+'" target="_blank" class="github-card-action">查看详情</a></div>'+
        '</section>';
        return html;
    }
    
    function renderUser(data) {
        var html = '<section class="github-card-avatar">'+
            '<img src="'+data['avatar_url']+'">'+
        '</section><section class="github-card-content">'+
            '<h4 class="github-card-title">'+data['name']+
            '<small>@'+data['login']+'</small></h4>'+
            '<p class="github-card-des">'+data['bio']+'</p>'+
            '<div class="github-card-footer"><ul class="github-card-meta">'+
                '<li>Followers '+data['followers']+'</li>'+
                '<li>Following '+data['following']+'</li>'+
            '</ul><a href="'+data['html_url']+'" target="_blank" class="github-card-action">查看详情</a></div>'+
        '</section>';
        return html;
    }

    var githubCardNum=0;

    $('[data-github]').each(function(){
        githubCardNum++;
        $(this).attr('id','github-card-'+githubCardNum);
        
        var card = this;
        var url;
        var info = $(this).attr('data-github');
        var info_array = info.split('/');

        var _token;
        if(token_ghOAuthClientID!='' && token_ghOAuthClientSecret!=''){
            _token = '?client_id='+token_ghOAuthClientID+'&client_secret='+token_ghOAuthClientSecret
        }

        //判断给出的数据是用户还是仓库
        if(info_array[1]!=''){
            //是仓库，则请求 api.github.com/repos/
            url = 'https://api.github.com/repos/'+info+_token;
        }
        else {
            //是用户，则请求 api.github.com/users/
            url = 'https://api.github.com/users/'+info_array[0]+_token;
        }

        $.get(url, function(data, status){
            if(status!='success') {
                return handleError(status);
            }else{
                if(info_array[1]!=''){
                    HTML = renderRepo(data);
                }else{
                    HTML = renderUser(data);
                }
            }
            $(card).html(HTML);
        });
    });
    
})();