Advanced-Sidebox 1.3.2c
===================

A plugin for MyBB forums that displays portal boxes on various forum pages.

Based on the code and concept of Nayar, this plugin seeks to give the admin more options and allow him to control the plguin in a more intuitive way. This plugin does not work with every theme, but is designed to work with most. It features several adjustments made to the original portal templates (which have been imported into the plugin and created as seperate editable templates) and enhancements in their operation and display.

Hooks have been installed into the plugin to allow other plugins to create and build sidebox types of there own that will add to the available box types in ACP to choose from. I have successfully integrated a test plguin into the structure. This plugin will probably become more extensible over time.

I will add more info as the project develops further.

Change Log
=========
01-16-2013 - 1.3.2c converted all files to UTF-8

01-16-2013 - 1.3.2b fixed invalid html output, added auto inserting empty block within tbody if user not provide any content for custom box

01-15-2013 - 1.3.2a fixed division by zero bug, added functionality to hide avatars when number of columns set to 0, added text output to staff online module.

01-14-2013 - 1.3.2 released with feature added by @avril-gh, added import/export functionality, addressed invalid HTML issues and other small bug fixes.

01-11-2013 - 1.3.1 released to address Issue #3 in which invalid HTML output caused left room for several possible errors.

01-10-2013 - 1.3 released adding the ability to expand/collapse sideboxes and remember their states using cookies. Also a bug fix to prevent a warning when there are no sideboxes installed yet.

01-06-2013 - 1.2 released adding independent script control and a renovated ACP page set. Admins can now create a different set of sideboxes for the four main scripts: index.php, forumdisplay.php, showthread.php and portal.php

12-31-2012 - 1.1 released to fix a logical error pertaining to function naming that created potential conflicts. Repaired faulty settings links.

12-26-2012 - 1.0 released
