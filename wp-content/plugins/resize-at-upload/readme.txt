=== Resize at Upload ===
Contributors: huiz
Donate link: http://dev.huiz.net/
Tags: image, plugin, resize, upload
Requires at least: 2.6
Tested up to: 2.7
Stable tag: 1.0

This plugin resizes uploaded images to a given width.

== Description ==

The plugin affects the uploaded image while it is uploaded. Straight
after it is uploaded, it it resized physically to the given width.
There is no original image left, nor made a backup. Also EXIF infor-
mation is lost.

A lot of WordPress users or developers who use WordPress as a CMS
and want to configurate it for easy use, would like to have an option
to resize uploaded images. Some images come straight from digital cameras
and exceed 4000 pixels in width. So it would be nice to reduce that to
the max width you use inside your WordPress theme.

If you want to keep control about perfect sharp and resized images, you
better use photo editing tools on your computer, not a script like this.

The plugin uses a class originally from Jacob Wyke (www.redvodkajelly.com).

== Installation ==

1. Upload the map 'resize-upload' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Make your settings through the 'Settings' menu in WordPress
4. Upload images while writing posts and pages.

== Screenshots ==

1. The settings (resize yes or no, the max width)
2. Full preview of the settings screen

== Frequently Asked Questions == 

Q. Why not reduce images in height?
A. The reason I wrote the plugin was to stop breaking the layout with
   images that are to large. Height is not always an issue. Maybe in
   future this option will be added.

Q. Does it keep my EXIF data?
A. No. Although it is programmatically possible to extract that data 
   first and put it somewhere in the metadata, which is not implemented
   off course, but a possibility, the data is lost in the process of
   resizing.
