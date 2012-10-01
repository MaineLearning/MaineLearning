=== BP Group Hierarchy ===
Contributors: ddean
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=6BKDCMJRYPKNN&lc=US&item_name=BP%20Group%20Hierarchy&currency_code=USD
Tags: buddypress, groups, subgroups, hierarchy, parent group
Requires at least: 3.0
Tested up to: 3.4.2
Stable tag: 1.3.5

Allows BuddyPress groups to have subgroups.

== Description ==

Break free from the tyranny of a flat group list!

This plugin allows group creators to place a new group under an existing group.  There is currently no limit to the depth of the group hierarchy.

Every group and subgroup is a normal BuddyPress group and can have members and a forum, use group extensions, etc.

= Translation =

* Spanish translation generously provided by <a href="http://dorsvenabili.com">_DorsVenabili</a>

== Installation ==

1. Extract the plugin archive 
1. Upload plugin files to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Does privacy or status propagate from group to subgroup? =

No. The plugin creates a hierarchy of group URLs, but does not put restrictions on the subgroup.

= Are group members automatically added to a subgroup? =

No. I don't know how you will want to use subgroups, so no assumptions have been made.

= If I restrict new groups to member or admins, can a subgroup be made with more lenient restrictions? =

Yes. Restrictions affect only the group to which they are applied.  Subgroups can themselves be more or less restrictive.

= Do activity stream messages propagate up (from child to parent) or down (from parent to child)? =

No. There is currently no way to have activity propagate up without creating duplicate entries in the sitewide activity stream.


== Screenshots ==

1. Group Tree tab on main Groups page
2. Member Groups item on individual group pages
3. Hierarchy options when creating new groups

== Changelog ==

= 1.3.5 =
* Changed: Made Javascript loading multisite-compatible
* Changed: devs - `bp_group_hierarchy_route_requests` action fires later; use `bp_group_hierarchy_globals_loaded` to access the original location
* Fixed: bug prevented loading group forum topic pages for subgroups - thanks, idjack
* Fixed: fatal error when viewing non-existent member profile (or possibly other 404 pages) - thanks, tangpage

= 1.3.4 =
* Added: faster saving of parent selection when creating a group on BP 1.6+
* Changed: slight speed-up filling in parent group dropdown with a large number of groups
* Changed: parent groups are sorted alphabetically in dropdowns
* Changed: plugin is no longer Network/Site Wide Only
* Changed: deprecated BP_Groups_Hierarchy::get_active() compatibility function
* Fixed: display bugs for anonymous users browsing group tree or member groups page - thanks, arialburnz

= 1.3.3 =
* Added: a filter for adding or removing toplevel group-creation permissions separately
* Changed: new method of loading templates for compatibility with privacy plugins - thanks, b1gft
* Changed: slight speed-up loading group tabs if you do not have the subgroup count in the Member Groups name string
* Changed: default behavior of 'anyone' permission: anonymous visitors will not see a Create Member Group button unless you
    enable it with the `bpgh_extension_allow_anon_subgroups` filter
* Changed: anonymous visitors can browse subgroups of Private groups -- disable by filtering `bp_group_hierarchy_allow_anon_access`
* Fixed: HTML title bug for Group Tree page
* Removed: more BP 1.2 leftovers

= 1.3.2 =
* Added: support for loading `hierarchy.css` from your theme directory so the group tree can better fit your site
* Added: `groups_hierarchy_create_group` function for creating groups with parents programatically
* Added: more debugging messages 
* Changed: deprecated `bp_get_groups_hierarchy_root_slug` wrapper function
* Changed: optimized path calculation for group pages
* Changed: reorganized plugin files to better fit BP coding standards
* Fixed: PHP warning that could occur on group pages
* Removed: BP 1.2 compatibility

= 1.3.1 =
* Added: new safeguards to alert admin when DB changes can't be made, and prevent fatal errors in some cases
* Added: strip HTML from page titles when displaying member group count in BP 1.2
* Changed: faster processing in BuddyPress 1.5 by only processing the current_action once
* Changed: column and key name syntax to reduce errors - thanks, nicosFR and imacg
* Changed: detection of deprecated BP title hook to avoid potential issue - thanks, tomraff

= 1.3.0 =
* Added: respect for setting `BP_GROUPS_HIERARCHY_SLUG` constant outside the plugin, for changing Member Groups URL
* Added: pagination self-sufficiency to BP_Group_Hierarchy_Template class
* Changed: default Member Groups text to reflect BP 1.5+ tab style
* Changed: wrap a subgroup count in "Member Groups" tab name in a span tag for proper display in BP 1.5+
* Changed: switched from deprecated upgrade file to the right one for plugin activation
* Fixed: bugs that triggered some warnings
* Fixed: debug functions respect `WP_DEBUG_DISPLAY` settings - thanks, rolandinsh
* Removed: ability to enable activity propagation - it will be fixed up and re-released, probably in an extras package

= 1.2.9 =
* Added: template function for getting a list of child groups
* Added: documentation to template functions
* Changed: made tree-loop template file more closely conform to latest groups-loop for theme editors
* Changed: bail when BuddyPress Groups component is disabled to avoid triggering fatal errors - thanks, 3dperuna
* Changed: extension uses `BP_GROUP_HIERARCHY_SLUG` constant instead of a separate value
* Fixed: cleaned up some older code that was triggering warnings
* Fixed: bug affecting profile plugins under the groups component - thanks, gg565

= 1.2.8 =
* Changed: updated Group Component to BuddyPress trunk
* Changed: switched Member Group sorting to alphabetical
* Fixed: a bug affecting Request Membership link - thanks, cyberhobo
* Fixed: a bug affecting permalinks for second level groups root_slug installs
* Fixed: a rare permalink bug that could create invalid URLs

= 1.2.7 =
* Added: `bp_group_hierarchy_group_tree_name()` function for template editors
* Changed: deprecated old translation scheme in favor of support for `load_plugin_textdomain()`
* Changed: updated `templates/tree/index.php` page to match the structure of BP 1.5.1 pages

= 1.2.6 =
* Added: new Group Navigator widget that shows member groups of the displayed group
* Added: sorting options for both widgets, including "Most Member Groups"
* Fixed: support for second level groups root_slug in "Create a Member Group" links - thanks to cyberhobo for catching this

= 1.2.5 =
* Added: save the parent ID of a new group when group is first saved (only when using the "Create a Member Group" button)
* Added: respect BuddyPress 1.5 "Restrict group creation to Site Admins" setting
* Changed: name of some extension functions for more consistent naming
* Fixed: Member Groups pagination in BuddyPress 1.5
* Fixed: don't try to load a template file from the plugin folder as a last resort unless it exists
* Fixed: handling of search placeholder text in BuddyPress 1.5 that caused empty group tree after using the sorting dropdown

= 1.2.4 =
* Added: Spanish translation generously provided by <a href="http://profiles.wordpress.org/users/_DorsVenabili/">@_DorsVenabili</a>
* Added: can enable activity propagation (but see FAQ for important info)
* Changed: string in the Top Level Groups widget to be more consistent with BP 1.5

= 1.2.3 =
* Changed: Group creation wizard error message to BuddyPress standard
* Changed: use groups->root_slug when available instead of groups->id
* Fixed: bug that would prevent site admins from creating a first group under certain circumstances

= 1.2.2 =
* Added: Block users from the group creation wizard when they aren't allowed to create groups anywhere
* Added: New debugging hooks for magic method errors
* Changed: improved Member Groups page display

= 1.2.1 =
* Added: pagination for Member Groups page
* Changed: improved compatibility with BuddyPress 1.5 

= 1.2.0 =
* Added: BuddyPress 1.5 compatibility (beta 2)
* Changed: made some filters more consistent
* Changed: workaround for issue with Doc in Nav (and other plugins that run on bp_setup_nav with priority 10) - thanks, @johnny2011
* Fixed: extension only loaded Group Tree if groups slug was 'groups' - thanks, @mutualdesigns

= 1.1.9 =
* Fixed: issues with the admin page and routing
* Fixed: title of the Groups Directory page when you hide the normal group list

= 1.1.8 =
* Added: BuddyPress 1.3 compatibility
* Added: template tags for group hierarchy
* Changed: disabled paging for subgroups on the Group Tree page

= 1.1.7 =
* Fixed: bug with my-group display reported by @pnerger

= 1.1.6 =
* Added: ability to restrict toplevel group creation to admins only

= 1.1.5 =
* Added: function to move child groups when deleting a parent
* Changed: file structure to match BuddyPress standard
* Fixed: short open tag in extension.php

= 1.1.4 =
* Added: 'Nobody' permission - allows only site admins to create child groups (req'd by @flynn)
* Changed: ID of widget panel to avoid interference with normal Groups widget
* Changed: Made default values for labels more consistent
* Fixed: Made group tree more resilient to invalid bp->groups->current_group data

= 1.1.3 =
* Added: support for searching and sorting when using only the Group Tree
* Fixed: Group Tree issue when there are more than per_page groups

= 1.1.2 =
* Fixed: Forum bug from the last update that affected the main Forums screen

= 1.1.1 =
* Added: Browse the entire hierarchy on the Group Tree page
* Added: Templates for listing groups and subgroups

= 1.1.0 =
* Added: top-level groups widget
* Changed: groups admins can edit subgroup creation permissions
* Changed: handling of parent group in group creation to avoid PHP errors
* Fixed: wrong URL on Group Tree tab - still requires AJAX loading, but getting closer

= 1.0.9 =
* Added: Ability to show number of child groups on the 'Member Groups' tab

= 1.0.8 =
* Added: Group Tree to extension for viewing groups by hierarchy
* Added: Admin options for Member Groups and Group Tree
* Changed: Create a Member Group button to hopefully resolve empty group slug issues

= 1.0.7 =
* Changed: extension brings the Member Groups tab into the BuddyPress loop
* Changed: behavior of check_slug method for self-sufficiency
* Fixed: Join and Leave Group buttons on Member Groups tab refer to parent group - thanks, @Deadpan110

= 1.0.6 =
* Fixed: bug that caused forum topics to not display reported by cezar

= 1.0.5 =
* Added: Group creators can now restrict subgroups to group members or group admins (with hooks for other types of restrictions)
* Added: Create a Member Group button on Member Groups tab for more streamlined use
* Changed: Reveal Member Groups tab to those allowed to create subgroups
* Changed: Default permissions now allow only group members to create subgroups
* Fixed: Private member groups were not being shown on that tab - thanks, @Deadpan110

= 1.0.4 =
* Added get_group_extras fixup for Group Forum Extras and others
* Fixed notification bug reported by @cezar

= 1.0.3 =
* Fixed bug when using custom group slug reported by @avahaf

= 1.0.2 =
* Fixed group invite bug reported by @cezar

= 1.0.1 =
* Fixed forum permalink bug reported by @mtblewis
* Added check_slug_stem function for wildcard searches
* More documentation

= 1.0 =
* Initial release

== Upgrade Notice ==

= 1.3.5 =
Bug fixes for group forum pages and 404 errors

= 1.3.4 =
Bug fixes for BP 1.6

= 1.3.3 =
Cleanup, template changes

= 1.3.2 =
Optimized group page loading, and reorganized files

= 1.3.1 =
Cleanup and bug fixes - LAST BP 1.2-COMPATIBLE RELEASE

= 1.3.0 =
Cleanup and bug fixes

= 1.2.9 =
Fixed bug affecting some profile plugins

= 1.2.8 =
Fixed permalink bugs, membership request bug, and changed Member Groups sort to alphabetical

= 1.2.7 =
Updated Group Tree template structure and localization support

= 1.2.6 =
Fixed composite group root slugs - thanks, cyberhobo

= 1.2.5 =
Fixed dropdown sorting bug on the Group Tree page in BuddyPress 1.5

= 1.2.4 =
Added Spanish translation, activity propagation, changed widget text to be more consistent

= 1.2.3 =
Fixed a bug preventing site admins from creating a first group under certain circumstances

= 1.2.2 =
Bar users from the create group wizard when they have nowhere to put a group

= 1.2.1 =
Added pagination for Member Groups page and misc. fixes

= 1.2.0 =
Updated for compatibility with BP 1.5, and fixed issues retrieving groups slug in some situations

= 1.1.9 =
Fixed issues with admin page and group routing. All users should upgrade.

= 1.1.8 =
Compatible with BP 1.3. Disabled paging to fix groups with more than 20 subgroups.

= 1.1.7 =
Fixed my-groups display bug. All users should upgrade.

= 1.1.6 =
Added ability to restrict toplevel group creation to admins. Last release for awhile; going to focus on 1.3 compatibility.

= 1.1.5 =
Mainly re-arranging files to prepare for the future. Also, prevent orphaned groups when deleting a parent

= 1.1.4 =
Increased compatibility with other group plugins, plus other minor changes

= 1.1.3 =
Fixed a bug when site has a large number of groups. All users should upgrade.

= 1.1.2 =
Fixed a bug with main forum list. All users should upgrade.

= 1.1.1 =
Browse the entire hierarchy from the Group Tree.

= 1.1.0 =
Added options for group admins and a top groups widget.

= 1.0.9 =
Changed Member Groups tab option.

= 1.0.8 =
Added admin options. 
May resolve empty group slug issue.

= 1.0.7 =
Fixed a bug affecting the Member Groups tab.
All users should upgrade immediately.

= 1.0.6 =
Fixed a bug that caused forum topics to not display
All users should upgrade immediately

= 1.0.5 =
Fixed an issue that hid private member groups
Added ability to restrict subgroups to member or admins

= 1.0.4 =
Fixed notification link bug
Users who want to use Group Forum Extras should upgrade

= 1.0.3 =
Fixed custom group slug bug
Users with custom BP_GROUPS_SLUG should upgrade immediately

= 1.0.2 =
Fixed group invite bug

= 1.0.1 =
Fixed forum topic permalink bug

== Known Issues ==

Currently known issues:

* Tabs on Groups page may revert to an "unselected" state when navigating the tree or hiding the normal group list
* Group Tree requires JavaScript
* PHP 5 only
