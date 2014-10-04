<?php
namespace DemoApp\Topic;
use CLIFramework\Topic\BaseTopic;

class BasicTopic extends BaseTopic {

    public function getTitle() {
        return 'Basic Tutorial';
    }

    public function getUrl() {
        return 'https://github.com/c9s/CLIFramework';
    }

    public function getContent() {
        return 
'        alternate object database
            Via the alternates mechanism, a repository can inherit part of its object database from another
            object database, which is called an "alternate".

        bare repository
            A bare repository is normally an appropriately named directory with a .git suffix that does not
            have a locally checked-out copy of any of the files under revision control. That is, all of the
            Git administrative and control files that would normally be present in the hidden .git
            sub-directory are directly present in the repository.git directory instead, and no other files are
            present and checked out. Usually publishers of public repositories make bare repositories
            available.

        blob object
            Untyped object, e.g. the contents of a file.

        branch
            A "branch" is an active line of development. The most recent commit on a branch is referred to as
            the tip of that branch. The tip of the branch is referenced by a branch head, which moves forward
            as additional development is done on the branch. A single Git repository can track an arbitrary
            number of branches, but your working tree is associated with just one of them (the "current" or
            "checked out" branch), and HEAD points to that branch.';
    }
}

