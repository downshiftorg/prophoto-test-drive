# ProPhoto Theme Test-Drive Plugin

**NOTE: Although the directions below are directed towards P5 users, this plugin works with any other theme, including P4.**

Because ProPhoto 6 is such a radical departure from version 5, the transition process between the two versions is not as seamless as in prior upgrades. ProPhoto 6 is not able to import your existing ProPhoto 5 design, so *there will be some time required on your part to re-design and re-brand your website.*

Because of this, we've created this plugin which allows you to keep ProPhoto 5 active *(showing it to all your site's visitors)* while you *(just logged-in admins)* see and use and customize ProPhoto 6.

If you'd like to test and work with ProPhoto6 on your production site while continuing to show your P5 design to visitors, please **follow the below instructions:**

### Step 1: Install this Plugin

To start, leave ProPhoto 5 as your active theme.  Install this plugin by following these steps:

1. In your WordPress admin area, go to "Plugins" > "Add New"
2. On the top of that screen, click "Upload Plugin" button
3. Download the zip file of this plugin by [clicking here](https://github.com/netrivet/prophoto-test-drive/archive/master.zip).
4. Upload the plugin zip file you just downloaded *(`prophoto-test-drive-master.zip`)*
5. After upload, click to activate the plugin

### Step 2: Install ProPhoto 6

ProPhoto 6 is installed like any other WordPress theme.  To do so, follow these steps:

1. In your WordPress admin area, go to "Appearance" > "Themes"
2. On the top of that screen, click "Add New"
3. After clicking "Add New", click "Upload Theme" on the top of the next screen
4. Upload the `prophoto6.zip` file you received in the email from us

**After uploading the `prophoto6.zip` theme file *do not activate it*. Instead, we will be *test-driving* it, as described below.**

### Step 3: Activate test-drive mode

If you're still running P5, have installed and activated the test-drive plugin, and have installed but *not* activated P6, you will now see a new admin notice in your WordPress admin screens:

![test-drive-notice](https://cloud.githubusercontent.com/assets/7050938/12378122/b4315bea-bd02-11e5-8eb2-0f531923a9de.png)

Click the link in the notice to start test driving. Once you click that link, you will be taken to a screen where you can register P6.  *It is very important that your register your copy of ProPhoto6, or else you will not receive critical bugfixes and updates during the beta period.*

### Step 4: Database Backup Plugin

Because ProPhoto 6 is still in beta, things may be moving and changing and you might need to restore a backup. If you choose to run P6 on your main production site, **you should be making regular backups of your database**.  If you are not already running a database backup plugin, follow these steps:

1. Download [this plugin](https://github.com/matzko/wp-db-backup/archive/master.zip).
2. In your WordPress admin area, go to "Plugins" > "Add New"
3. Click the "Upload Plugin" button at the top of the screen
4. Upload the file you downloaded in step 1 above
5. After upload, click to activate the plugin
6. Go to "Tools" > "Backup" and set an hourly scheduled backup to be sent to your email. Be sure to check to have all of the extra ProPhoto tables (shown below, on right) also backed up.

![backup](https://cloud.githubusercontent.com/assets/7050938/12275677/5dde8fc2-b93f-11e5-9e9c-2a781fe58b0b.png)

*If you use something like Gmail, you can set up a filter to have these emailed backups go automatically to a folder, or to the trash, where you can always dig them out in case of need, but won't have to see them in your email every hour.*

### Design and Test

That's it!  You now have ProPhoto 5 running for all of your site visitors (you can verify this by opening up a different browser altogether where you are not logged in and viewing your site), but ProPhoto 6 running for you when you are logged into your site.

Go ahead and start working on your P6 design, testing things out, and submitting feedback and questions as needed.  You can take your time getting used to the new theme while serving your old P5 site uninterrupted.
