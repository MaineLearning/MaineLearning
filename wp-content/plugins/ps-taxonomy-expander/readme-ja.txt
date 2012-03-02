=== PS Taxonomy Expander ===
Contributors: jim912
Tags: category, tag, taxonomy, custom taxonomy
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 1.1.6

カテゴリー、タグ、カスタム分類の使い勝手を向上させます。

== Description ==
PS Taxonomy Expanderは、カテゴリー・タグ、そしてカスタム分類の記事編集時における使い勝手を向上させます。

= Functions =
1. カテゴリーのような階層化されたタクソノミーの登録をラジオボタンと切り替えることができます。
2. サポートする投稿タイプ毎に、デフォルトの分類を設定することができます。
3. メディアのカスタム分類メニューを追加します。
4. 階層化されたメディアのタクソノミー登録方法をチェックボックス/ラジオボタンに変更します。
5. プロフィール設定にて、ダッシュボードの現在の状況にカスタム分類を表示追加させることができます。
6. タクソノミー毎の順序を指定することができます。
7. 記事一覧ページにて、カスタム分類の表示追加、カスタム分類での絞り込みをすることができます。

= Usage =
* 投稿設定ページに、デフォルト分類、登録方法の設定が追加されます。
* プロフィールページ、現在の状況へのタクソノミー表示設定が追加されます。
* 設定メニューに、"Term order"メニューが追加されます。
* 投稿設定ページに、カスタム分類の記事一覧への表示設定が追加されます。

== Installation ==

1. pluginsフォルダに、ダウンロードした PS Taxonomy Expander のフォルダをアップロードしてください。
2. プラグインページで "PS Taxonomy Expander" を有効化して下さい。
3. 投稿設定で分類の登録方法、初期分類、一覧表示への追加の設定ができます。Term orderメニューでは、順序指定が可能です。

== Changelog ==
= 1.1.6 =
* 投稿一覧画面でNoticeエラーが発生する問題を修正（wp_deregister_scriptからwp_dequeue_scriptに変更。3.0対応として、wp_dequeue_scriptのコピーを同梱）
= 1.1.5 =
* 3.3でクイック編集クリック時に行が消える問題を修正
= 1.1.4 =
* 記事一覧へのカスタム分類表示が他のカスタム表示項目に影響してしまう問題を修正
= 1.1.3 =
* Warningエラーの修正
= 1.1.2 =
* 記事一覧にカスタム分類の表示、絞り込み機能を追加。
= 1.1.1 =
* Term orderメニューを独立化
= 1.1.0 =
* 分類の順序指定機能を追加
= 1.0.1 =
* バグフィックス
= 1.0.0 =
* メディア分類がギャラリーで編集・保存できない問題を修正

= 1.0.0 =
* Notice, Warningエラーの修正
* 単数形・複数形表示の修正
* 現在の状況へのカスタム分類表示機能追加
* ローカライズ対応

= 0.8.0 =
* Public release

== Screenshots ==
1. 階層化分類のラジオボタン登録
2. デフォルト分類設定とカスタム分類の一覧表示設定
3. メディア分類のメニュー表示と登録方法
4. 現在の状況にカスタム分類の表示
5. Term orderの設定画面
6. 記事一覧へのカスタム分類

== Links ==
"[PS Auto Sitemap](http://wordpress.org/extend/plugins/ps-auto-sitemap/ "WordPress sitemap plugin")" is a plugin automatically generates a site map page from your WordPress site. 
It is easy to install for beginners and easy to customize for experts.
It can change the settings of the display of the lists from administration page, several neat CSS skins for the site map tree are prepared.

"[PS Disable Auto Formatting](http://wordpress.org/extend/plugins/ps-disable-auto-formatting/ "WordPress formatting plugin")"
Stops the automatic forming and the HTML tag removal in the html mode of WordPress, and generates a natural paragraph and changing line.

"[CMS service with WordPress](http://www.web-strategy.jp/ "CMS service with WordPress")" provides you service that uses WordPress as a CMS.

== Special Thanks ==
English Translation:[Odyssey](http://www.odysseygate.com/ "Translation")