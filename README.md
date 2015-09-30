# Advanced Custom Fields: Commits

**Stable Version:** v0.1.0

**NOTE:** This plugin only currently supports Advanced Custom Fields PRO and does not work with the free version
of Advanced Custom Fields. Support for free ACF is planned for the near future.

## What is Advanced Custom Fields: Commits?
ACF Commits is an add-on for [Advanced Custom Fields](http://advancedcustomfields.com/) which allows developers to
maintain version control over changes made to Field Groups.

## Installation

ACF: Commits is a WordPress plugin with a dependency on the Advanced Custom Fields plugin.

Currently, only the PRO version of the plugin is supported. Support for the free version is planned for the near future.

1. Install the Advanced Custom Fields plugin to your WordPress installation: [Advanced Custom Fields](http://www.advancedcustomfields.com)
1. Download the archive of the project via [GitHub](https://github.com/2nickpick/acf-commits/archive/master.zip)
1. Install the ACF: Commits plugin to your WordPress installation


## Features

### Create a commit
1. Add, Edit or Delete an ACF Field Group
1. Before completing your changes, you'll be asked to enter a commit message. You won't be allowed to
publish until you do.
1. Once you have at least one commit, you'll be able to review any changes to the current Field Group
just below the Update button. Click the timestamp of the commit to view its details.

![Commit Message Required](/assets/commit_message.png "Commit Message Required")

![Ready to Commit](/assets/commit_message_ready.png "Ready to Commit")

![Commit History](/assets/commit_listing.png "Commit History")

*Hint:* You can also access the entire change history via the "Modification Log" submenu under the Custom Fields menu.

### Restore ACF to a previous state
1. Visit the Modification Log, either via the submenu under Custom Fields, or a commit on the Edit Field Group page.
1. You can review what the export consists of via the Export action. Currently, only the JSON view is available.
1. You can restore the database to a previous version of ACF via the "Revert" action. This will remove any ACF Fields
or Field Groups and import the fields at the time of the export chosen.

![Commits](/assets/commits.png "Commits")

## Contents

The contents of the plugin are described in detail:

* `\assets`. Screenshots for demo
* `\css`. Directory stores styles used by the plugin
* `\js`. Directory stores scripts used by the plugin
* `\languages`. Directory stores the textdomain of the plugin
* `\includes`. Directory stores any extra PHP scripts used by the plugin
* `index.php`. Throwaway file to prevent directory access on poorly configured servers
* `acf-commits.php`. The core of the plugin
* `CHANGELOG.md`. The list of changes to the core project.
* `README.md`. The file that you’re currently reading.

## License

ACF: Commits is licensed under the GPL v2 or later.

> This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License, version 2, as published by the Free Software Foundation.

> This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.

> You should have received a copy of the GNU General Public License along with this program; if not, write to the Free Software Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA 02110-1301 USA

# Credits

Advanced Custom Fields: Commits was started in 2015 by [Nick Pickering](http://github.com/2nickpick/).

Advanced Custom Fields is developed by [Elliot Condon](http://www.elliotcondon.com/).

## Contact

We welcome pull requests on GitHub! If you have any issues submit them via GitHub!

[GitHub ACF: Commits](https://github.com/2nickpick/acf-commits)
