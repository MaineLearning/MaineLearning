=== BP GTM System ===
Contributors: slaFFik, valant
Tags: buddypress,tasks,task management,projects,project management,ajax,community,groups,management,todo,todo list,crm
Tested up to: WP 3.3, BP 1.5.3.1
Stable tag: 1.9
Requires at least: WP 3.3, BP 1.5

BP GTM System will turn your site into a developer center, where tasks, projects, discussions, categories and tags will help you maintain products.

== Description ==
BuddyPress Group Tasks Management (BP GTM) System helps you control the development process using tasks and subtasks that correspond to some projects, tags and categories. Responsible people can be chosen, everything can be discussed.

= Tasks, Subtasks and Projects =

* create with TinyMCE
* edit / delete everything
* mark as completed/pending
* filters (lots of options)
* change project it corresponds to
* change responsible people (they will be notified)
* change tags using autocompletion, categories from a list
* roles/capabilities management
* attach files to tasks, projects and discussion posts
* and other unique features

= Some other things =

* discussions
* personal assignments page
* notifications (PM, mentions, emails)
* autocompletion for tags / categories

== Changelog ==

= 1.9 (03.02.2012) =
* Introducing files management for projects, tasks, discussion posts
* Inherit WP dates (so the date format is unified)
* Improved Involved page
* Improved UX for selecting/adding tags and categories
* Some bugs fixes and code improvements

= 1.8.1 (21.01.2012) =
* CSS fixes

= 1.8 (20.01.2012) =
* Fixed bug while group creation
* Fixed bug with roles on Involved page
* Fixed bug with Save Tasks button when there are no projects created
* Fixed bug with displaying projects/tasks creation notices in activity stream
* Added ability to change group ToDo menu label into smth group-specific
* Added support for custom templates for GTM in currently active WP Theme
* Major code cleanup

= 1.7.4 (16.01.2012) =
* Minor code improvements
* More secure while saving data
* Fixed several hard-to-catch bugs on some environments

= 1.7.3 (13.01.2012) =
* Fixed bug with activation on child themes
* Updated ru_RU.po
* Some minor css improvements
* Sponsored by Bluecare from Switzerland

= 1.7.2 (10.01.2012) =
* Fixed problems in admin area for Network activated site
* Fixed bug with DB reinstalling
* Fix lack of text on Classifier page if no tags were created
* Code cleanup (php, js and css)
* Sponsored by Bluecare from Switzerland

= 1.7.1 (07.01.2012) =
* Found and fixed js error in admin area (Roles box, Action button)
* Minor css fixes
* Sponsored by Bluecare from Switzerland

= 1.7 (06.01.2012) =
* Updated view for selecting responsible people on creation/updating tasks/projects pages
* Ajaxified categories for tasks/project, updated the same fot tags
* Validation things for tasks/projects
* Fixed compatibility with [Connections plugin](http://wordpress.org/extend/plugins/connections/)
* Fixed bug with template handling when upgrading from 1.0.x
* Code cleanup (php, js and css)
* Sponsored by Bluecare from Switzerland

= 1.6 (27.12.2011) =
* Choosing responsible people for tasks/projects from group members only
* New view for choosing responsible people
* Choose categories the same way as for tags
* Some code improvements and cleanups
* Sponsored by Bluecare from Switzerland

= 1.5 (21.12.2011) =
* BuddyPress 1.5 support
* Fixed lots of bugs (with pagination, roles, css etc)
* Sponsored by Bluecare from Switzerland

= 1.0.1 =
* Several fixes for tasks creation/editing system (props to [@imath](http://buddypress.org/community/members/imath/))
* Fixed MySQL mistype that prevented from resps' table creation (props to [@imath](http://buddypress.org/community/members/imath/))
* Added a display whether subtask is completed or not for current task (in a list under parent task description)

= 1.0 =
* Privacy and GTM role management – now you can specify what each user can and can't do
* Importing users – users that were in groups and didn’t have roles will know have one defined by you
* Default roles for group newcomers and new group creators
* Create new roles and manipulate with their actions
* Change GTM system role of a user in a group on a fly – with special admin actions (like BP Admin Actions plugin does)
* Display tasks/projects creation in activity feed ([user] created a [task|project] – [name])
* Display discussion post creation in activity feed ([user] posted a comment to the [task|project] – [name])
* Display/hide personal Assignments link in My Account of every user
* Rename GTM System menu and personal Assignments links labels to what you wish
* Lots of bug fixes
* Code improvements

= 0.9.6 =
* Added tasks and projects id filter: use #T112 or #P13 in tasks/projects descriptions and discussion posts, where numbers are id of needed task or project respectively. This mention will be converted into a link to appropriate task or project
* Added ability for group/site admins to remind involved people about pending tasks/projects they are responsible for (see Involved page - Actions) via personal email

= 0.9.5.1 =
* Fixed bug with lack of MySQL type of 1 field (props to @sinclairfr)
* Added French translation (props to @sinclairfr)
* Added German translation (props to @david)

= 0.9.5 =
* First public release with all the features from this [About page](http://gtm.ovirium.com/about/version-0-9-5)


== Installation ==
1. Upload files to /plugins/ directory
1. Activate plugin on Plugins page
1. Go to "BuddyPress -> GTM System" page, customize it
1. Enjoy!

== Frequently Asked Questions ==
None yet

== Screenshots ==

You can test this plugin on my demo site [GTM.Ovirium.com](http://gtm.ovirium.com/)

1. Admin page
2. Projects list
3. Tasks list
4. Classifier
5. Widget
6. Discussions page
7. Settings page
8. Personal page
9. Involved page
