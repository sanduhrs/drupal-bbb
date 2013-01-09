# BigBlueButton for Drupal 7

## Source Code // Project Pages:
* BigBlueButton: http://code.google.com/p/bigbluebutton
* BigBlueButton for Drupal: http://drupal.org/project/bbb

## Installation and Setup:
1. Install a Working BigBlueButton Server: http://code.google.com/p/bigbluebutton/wiki/08InstallationUbuntu
2. Generate a Salt Code on BigBlueButton Server side with the following command: $ bbb-conf --salt
3. Download current dev version of BBB module 
4. Enable BBB module
5. Configure BBB Settings at: admin/config/media/bigbluebutton by adding Base URL and Security Salt (Note: It's important there is no trailing / on the URL!)
6. Create a new content type or edit an existing node type to "treat this node type as a meeting"
7. Create or edit a node of your new type and ensure the bigbluebutton settings are correct.