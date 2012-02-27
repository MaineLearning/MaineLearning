## Installation process (for new dev or staging installs)

This process assumes you have Git installed on your local machine. Visit http://help.github.com/mac-set-up-git/ for a nice guide.

1) Create a new database on the local machine.

2) Download the latest database dump from the production site (~/dbdumps/)

3) Import the database dump to your newly created database

    mysql -u yourusername -p yourdatabasename < ~/path/to/the/downloaded/sqldump

4) Create a new Apache-accessible directory

5) Clone the Git repository into that directory:

    git clone git@github.com:MaineLearning/MaineLearning.git /path/to/your/local/directory/
    
6) Once the clone is finished, copy env-sample.php to a new file env.php. Enter the approprate DB-specific config data into this new file.

7) From the production site, download wp-content/blogs.dir and wp-content/uploads to the corresponding directories in your local installation.

8) Create a new directive in your /etc/hosts file:

    127.0.0.1   mainelearning.net

9) Create a new Apache virtual host on your local machine for mainelearning.net. Instructions for MAMP: http://foundationphp.com/tutorials/vhosts_mamp.php (google for other setups). Restart Apache when you're done for the changes to take effect.

10) Visit http://mainelearning.net. You should see the site, with the Local Environment flag in the lower right.

## Development workflow

### Overview

The guiding principle for development is that the file system on the production environment is never touched, except at deployment. All development happens in local dev environments. All changes are tracked via Git, and shared with the rest of the development team via the shared Github repository. 

### Branching philosophy

At any given time, there will be three active shared branches in the Github repository: 

- The stable branch will be named after the current dot-dot series. So if the current release number is 1.3.5, the current stable branch is 1.3.x. The purpose of this stable branch is for the fixing of bugs that will go into minor releases. This branch should (as the name suggests) remain stable at all times.
- The feature development branch will be named after the upcoming dot/feature release. In the case described above, our feature branch would be 1.4.x. All new feature development/all items in the 1.4 milestone go in this branch.
- The master branch is for releases. No development should take place on the master branch - only the release manager will ever touch this branch. See the 'Deployment' section below for more details.

### Bug fixing and feature development (day-to-day dev)

In what follows, I assume only a rough working knowledge of Git. If you are already familiar with using Git for shared development, skip to the end of this section for the Short Version.

#### The long version

The first thing you should do when sitting down to work on anything is to check the status of your local repository.

    git status

The first line returned will tell you what branch you are on. (See below for more details on the branching philosophy.) If you're on the wrong branch, switch to the correct one with:

    git checkout [branchname]

If you have any local changes (`Changed but not updated` or `Changes to be committed`), either stash them or commit them before continuing.

Before beginning development, pull the latest changes from the proper branch on the shared repository. For branch 1.3.x:

    git pull origin 1.3.x

If you are making changes beyond the extremely trivial, it's recommended that you start a new local branch. To create a branch, use `git checkout` with the `-b` flag:

    git checkout -b mybugfixbranch
    
When you're ready to commit your changes to your local repository, first you'll need to stage them, using `git add` on each file/directory with changes.

    git add wp-config.php
    git add wp-content/themes/my-theme/index.php
    git add wp-content/themes/my-theme/images
    
Note that `git add` is recursive by default. You can now commit:

    git commit -m "This is my commit message."

Your commit message should contain the following information:

    - a brief description of the change
    - a reference to an issue number where the issue is discussed in greater depth, and/or a more in-depth description of the problem being solved
    - references to any other relevant changesets or tickets

Commit messages are a major way of building a project history and communicating your thought processes with other developers on the team, so take the time to have descriptive commit messages.

These commits are _local_. In order to share them with the group, you've got to push them to the shared Github repo:

    git push origin 1.3.x

If you get a message that Git has prevented your push because it can't fast-forward, it means that the remote branch has been modified since your last pull (another developer has pushed to it). Re-pull, which will force a merge, after which you can try pushing again.

#### Database-level changes

WordPress keeps a lot of configuration data in the database rather than the file system, making it more difficult to track. If you commit a change that requires a change to the database - for instance, if you add a theme that needs to be enabled, or a plugin that needs to be activated, or you change settings somewhere - make sure to do both of the following:

- Mention this change in the commit message, using the flag ACTION_REQUIRED. For example,
    
    git commit -m "Adds the WordPress plugin BuddyPress Awesometown. Fixes #33; see also #18. ACTION_REQUIRED: Network activate the plugin."

- Record the change under the appropriate release header in actions_required.md. For example,
    
    ### 1.3.7
        
    - Network activate the plugin BuddyPress Awesometown
        
#### The short version

- Make sure you're developing off the right branch before doing anything (the stable branch for bugfixes, the feature branch for new features). The master branch is for releases only (see 'Deployment' below).
- Use verbose commit messages, with fix descriptions, and either a reference to the related ticket, or an extended description of the problem you're fixing (or both).
- When your changeset requires a database-level change, note it in your commit message with the flag ACTION_REQUIRED, as well as a note in actions_required.md. (See 'Database-level changes' above.)


## Deployment

The production site is a clone of the Github repo, just like your dev environment. It runs the master branch at all times, which is dedicated to final releases. The workflow for the release manager is like this:

- Merge the latest changes from the source branch (the stable branch for bugfix releases, the dev branch for feature releases) into the master branch
- Use the .htaccess flags to do an IP block, and change the maintenance file to give up-to-date information. Commit these changes.
- Push the master branch to the Github repo
- From the production server, pull the latest master branch
- Grep the `git log` for ACTION_REQUIRED, and pull up actions_required.md for cross-reference. Do any of the required actions.
- Do any necessary testing
- When everything looks OK, remove the .htaccess restrictions, commit the changes, and push back to Github master. The release is live.
- On the local machine, pull the latest changes.
- If any substantive changes were necessary on the production server, cherry-pick them to the appropriate branch
- If this is a bugfix release, merge --no-ff the bugfix branch into the dev branch, so that all bugfixes are applied to the dev branch. If this is a feature release, then the old dev branch has become the bugfix branch, and you'll need to create a new dev branch named after the *next* feature release.
- Push all changes
- Make sure the Github issues milestone is cleared, and a new one is created for the new release
