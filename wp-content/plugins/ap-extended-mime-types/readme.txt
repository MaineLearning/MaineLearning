
=== AP Extended MIME Types ===
Contributors: hornetok
Plugin Name: AP Extended MIME Types
Donate link: http://ardentpixels.com/josh/contact
Tags: mime-types, MIME, multisite, media, upload, wpms
Author URI: http://ardentpixels.com/josh/
Author: Josh Maxwell, Ardent Pixels
Requires at least: 2.0
Tested up to: 3.3.1
Stable tag: 1.1

This plugin extends the allowed uploadable MIME types to include a WIDE range of file types. Created specifically for WPMS...


== Description ==

The [Ardent Pixels'](http://ardentpixels.com/) *Extended MIME Types* plugin was created specifically for WPMS in mind. You can now allow all or only select blogs to upload a WIDE range of file types.

= Included MIME Types: =
* ac3
* ai
* aif
* aifc
* aiff
* au
* avi
* bmp
* cat
* clp
* crd
* css
* csv
* csv
* dll
* doc
* docm
* docx
* dot
* dotm
* dotx
* eps
* flv
* gif
* gtar
* gz
* gzip
* ics
* ief
* ifb
* jpe
* jpeg
* jpg
* js
* m13
* m14
* mdb
* mid
* midi
* mny
* mov
* movie
* mp3
* mp4
* mpa
* mpe
* mpeg
* mpg
* mpp
* msg
* mvb
* pdf
* pict
* png
* pot
* potm
* potx
* ppam
* pps
* ppsm
* ppsx
* ppt
* pptm
* pptx
* ps
* pub
* qt
* ra
* ram
* rtf
* rtx
* scd
* snd
* sst
* stl
* swf
* tif
* tiff
* trm
* tsv
* txt
* w6w
* wav
* wmf
* word
* wri
* xla
* xlam
* xlc
* xlm
* xls
* xlsb
* xlsm
* xlsx
* xlt
* xltm
* xltx
* xlw
* zip


== Installation ==
= Single WordPress Install = 
1. Upload the **ap-extended-mime-types** folder to the **/wp-content/plugins/** directory
2. *Activate* the plugin through the *Plugins* menu in WordPress

= WordPress MultiSite Install =
1. Upload the **ap-extended-mime-types** folder to the **/wp-content/plugins/** directory
1. *Network Activate* the plugin through the *Network Admin > Plugins* menu in WordPress
  **--OR--**
1. *Activate* the plugin through the *Plugins* menu in WordPress on an individual site basis


== Changelog ==

= 1.1 =
* ap-extended-mime-types.php, line 44: changed double quotes to single
* mime-types.txt: changed commas to pipettes
* "doc,dot,word,w6w" now upload correctly
* Removed .htm & .html as allowed file types

= 1.0 =
* First release


== Frequently Asked Questions ==
= Do I need to do anything else for AP Extended MIME Types to work? =
Nope, just upload and activate. There are not options, so the code'll do the rest.

= How do I add a new MIME type? =
Edit *mime-types.txt* to add/remove MIME types. Add your new MIME type in this format: *extension content/type*. For example:

* pdf application/pdf
* mid,midi audio/midi

For more file and formatting examples, view the *mime-types.txt* file. Also, a full list of MIME types is kept up on the [IANA](http://www.iana.org/assignments/media-types/index.html "Internet Assigned Numbers Authority") website.

= What in the world is a MIME? =
No, MIMEs are not white-faced, beret-sporting people who live in a *"box"*. MIMEs are [Multipurpose Internet Mail Extensions](http://en.wikipedia.org/wiki/Internet_media_type).


== Upgrade Notice ==
N/A


== Screenshots ==
No screenshots -- just code.


== Notes ==
= Donations =
Feel free to [donate](http://ardentpixels.com/josh/contact/) if you liked this plugin.