# BracketDown Plugin for Typecho

BracketDown 是一个适用于 Typecho 的 Markdown 语法拓展插件，加入了一系列短代码，方便博主在编写文章时更好地排版。

## 使用

1. 下载仓库 `main` 分支中的文件，或者使用 `git clone`
2. 将插件目录重命名为 `BracketDown`（注意大小写）
3. 将插件文件夹放置于 typecho 安装目录下的 `/usr/plugins/` 目录
4. 在 Typecho 后台启用插件
5. 按照设置界面的提示配置插件

Enjoy~

## 注意事项

Typecho 原生的代码块内不能出现 BracketDown 的语法代码，它们会被照常解析，这样往往会打乱你的排版，让文章变得很奇怪。
只有你真的要使用相应的功能时才可以输入这些代码。

同时，请避免在文章中出现额外的 `[]` `[` `]`，这会更加严重的打乱你的排版，目前暂时无法修复，总之，如果不使用短代码就不要输入中括号！

## 语法

这里将详细介绍 BracketDown 插件拓展的语法

### 文字块（Block）

文字块用于让一部分文段以更加显眼的方式展示出来，默认样式的文字块是一个有淡灰色边框和浅灰色背景的盒子。
通常情况下文字块用于标记正文中较为重要的额外信息，很多时候博主会用块引用（Blockquote）来完成同样的事情，即使那些文本并非是引用，在这样的情况下，文字块是比块引用更好的选择。

你可以这样创建一个文字块。

```
[block]你要标记的文段。[/block]
```

### 下拉框（Details）

顾名思义，下拉框语法可以创造一个默认只显示标题，点击后就会展示详细内容，再次点击则会收起的结构，用于书写一些不那么重要但可以用于参考的信息。
BracketDown 的下拉框使用的是 HTML 原生的 `<details>` 标签，这个特性比较新，所以插件引入了相应的垫片来提高其兼容性。

你可以这样创建一个下拉框。

```
[details sum="默认显示的标题"]点击后展开的内容[/details]
```

or

```
[details]
[summary]默认显示的标题[/summary]
点击后展开的内容
[/details]
```

推荐使用第一种方法。

### 网格（Grid）

如果你了解前端设计中的网格/栅格，那么你能很轻易地上手这个语法。如果你不了解，这里做一些简单的说明。
网格语法可以让文章内容分列显示，类似于没有表头和边框的表格。这里需要理解两个概念：行（Row）和列（Column）。
任何列都应当放置在行当中，行的宽度固定为 12，而列的宽度则可以取 1~12 的整数。
举个例子，如果一个行里有两个列，其中一个列的宽度是 4，另一个列的宽度是 8，那么它们就各占这一行的 1/3 和 2/3。

你可以这样创建网格。

```
[row]
[col grid="4"]我占这一行的 1/3[/col]
[col grid="2"]我占这一行的 1/6[/col]
[col grid="6"]我占这一行的 1/2[/col]
[/row]
```

其中，`[row]` 用于创建行，是必不可少的；`[col]` 用于创建列，其中 grid 属性定义了该列的宽度。

大部分情况下使用的最多的宽度是 6，也就是一行的一半，为了更快捷的使用网格将一行一分为二，你可以使用以下方法创建行。

```
[row]
[half]我占这行的一半[/half]
[half]我占这行的一半[/half]
[/row]
```

在移动端，屏幕的宽度变窄，如果这时候列的宽度太小，文章内容就会被挤压成长条状，难以阅读。
为了解决这个问题，BracketDown 兼容了 Bootstrap 网格的响应式设计，也就是可以设置网格在多大的屏幕下才会生效。

具体做法如下。

```
[row]

[col grid="md-6"]这一列只有在中等及以上大小的屏幕上才会生效（最常用）[/col]
[col grid="sm-6"]这一列只有在小型及以上大小的屏幕才会生效[/col]
[col grid="lg-6"]这一列只有在大型及以上大小的屏幕才会生效[/col]

[md-half]同理[/half]

[/row]
```

### 文字上色（TextColor）

有些时候我们可能会想要给一段文字加上背景色或者改变它的颜色，用于强调或者装饰，可以这样写。

```
&{被上色的文字|颜色|背景色}
```

如果只想要更改文字颜色和背景色其中的一个，则可以这样写。

```
&{被上色的文字|颜色}
#{被上色的文字|背景色}
```

其中，颜色和背景色都可以用 css 中能够使用的方式表示颜色，例如：英文单词（red/yellow/white...）、十六进制颜色代码（#000/#66ccff/#fefefe...）、RGB 值（rgb(0,0,0)/rgba(0,0,0,1)...）等。

### 下划线

可以用以下语法给一段文字添加下划线，默认样式为 `text-decoration: underline`。

```
?画线的句子?
```

### 注音

遇到一些生僻字~~或者纯粹想要调皮一下~~的时候，可能会需要给文字注音。

```
{{文字:拼音}}
```

### 键盘按键

在表示键盘上的按键，如 `ctrl`、`tab` 等，可以使用以下语法，创建一个外观与按键类似的小框。

```
[[键盘按键]]
```

