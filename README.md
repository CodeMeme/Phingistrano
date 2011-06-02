# Phingistrano #
A PHP utility for building and deploying projects based on Phing and other paralell technology.

## Table of Contents ##
* [Overview][overview]
* [Command line usage][commandline]
* [Installing Phing and dependencies][dependencies]
* [Adding Phingistrano to a project as a git submodule][submodule]
* [Creating a build file][build]
* [The Modules][modules] 
    + [Deploy][deploy]
    + [Rollback][rollback]
    + [Test][test]
    + [Version][version]
    + [Sniff][sniff]
    + [Docs][docs]
* [Gotchas][gotchas]
* [About][about]  

<a name="overview" />  

[overview]: #overview
## Common build and deploy repository ##
This is an attempt to keep all of our build utilities in a common centralized repository that can be loaded as a submodule. The hope is that we can add this submodule to any existing project and immediately use its build/deploy functionality, pending some property/configuration changes.  

Phingistrano tries to be flexible by loading it's individual components as modules by the Phing *importTask* task. This repository does not contain the main build.xml to use, but it does give an [example of what one might look like](https://github.com/CodeMeme/Phingistrano/blob/master/build.example.xml) .  

It's important to understand that by this paradigm, you must tailor your buid.xml to the project, and import these submodules as needed. If you need customized versions of individual components, you could simply override or use different tasks defined in your build.xml, or even better, [import a custom build.helpers.xml](https://github.com/CodeMeme/Phingistrano/blob/master/build.helpers.example.xml) . In the examples I've shown, the targets are just aggregated targets from within the modules, strung together with the *depends* attribute. If you look at how it's done, you will likely see how easy, clean and flexible this is.  

<a name="commandline" />  

[commandline]: #commandline
## Command line usage ##

* phing help (lists the available targets in the project and modules)
* phing [ target ]  executes a target in the main project
* phing [ module ].[ target ] executes a target in a submodule

<a name="dependencies" />  

[dependencies]: #dependencies
## Installing Phing and dependencies ##

This repository uses phing, git and several phing and php extensions which are required
to use all of the features. I will try to outline how to get and install these dependencies. 
I'm using Ubuntu 10.10 as a dev machine but I've also done this on Fedora 13 and RHEL 6 with equal 
success. You may need to slightly tweak these directions to suit your environment but this should
get you started.

### Git ###

Git is currently the only repository system supported by this package.  

    sudo apt-get install git

### Phing is the interface ###

[Phing 2.4.5](http://phing.info/trac/wiki/Users/Download)  

    sudo pear channel-discover pear.phing.info
    sudo pear install phing/phing

### PHP Codesniffer ###

    sudo pear install PHP_CodeSniffer-1.3.0a1

I could not get phing 2.4.4 to work with this version (1.3.0.RC1) of phpcs.
Phing seems to want to use the "-o" flag with phpcs and the error message suggest that 
1.3.0.RC1 does not support that flag. Therefore I recommend using version PHP_CodeSniffer-1.3.0a1
[PHP_CodeSniffer-1.3.0a1](http://pear.php.net/PHP_CodeSniffer) 

### PHPUnit ###

[PHPUnit by Sebasian Bergmann](https://github.com/sebastianbergmann/phpunit/)

*Warning* phing will not currently work with phpunit 3.5, only phpunit 3.4 and lower is supported.

The PEAR channel (`pear.phpunit.de`) that is used to distribute PHPUnit needs to be registered with the local PEAR environment. Furthermore, components that PHPUnit depends upon are hosted on additional PEAR channels.

    pear channel-discover pear.phpunit.de
    pear channel-discover components.ez.no
    pear channel-discover pear.symfony-project.com

This has to be done only once. Now the PEAR Installer can be used to install packages from the PHPUnit channel:

    pear install phpunit/PHPUnit

### PHP Documentor ###

We use php documentor in our build routine to document our code.  
 
    sudo pear install --alldeps PhpDocumentor

### VersionControl_Git-alpha ###

This tool needs an extended git library for phing

    sudo pear install VersionControl_Git-alpha

Also included in the repo, these phing git extensions by Evan Kaufman:
[Extended Git tasks](http://github.com/EvanK/phing-ext-gittasks)

### Pear mail extension ###

    sudo pear install pear/Mail
    sudo pear install pear/Mail_Mime

### SSH2 PHP Extension ###

This repo needs the php ssh2 extension. 
I have 2 sets of instructions for installing this depending on the operating system so only follow 1 set of instructions as it applies to the system  you are on.

### Install ssh2 library on Fedora 13+ &RHEL 6+ ###

    yum install libssh2-devel


### Install ssh2 library on Ubuntu 10.10+ ###

##### Install the PHP Bindings for libssh2 #####

    sudo apt-get install libssh2-1-dev

##### then download and install the ssh2 library with git #####

    sudo apt-get install libssh2-php

### SSH2 PHP Extension (all operating systems) ###

Once you've installed the SSH2 library, install the PHP extension with PECL  

    sudo pecl install channel://pecl.php.net/ssh2-0.11.2

#### Configure extension ####

The ssh2 extension might not add itself to the php ini. 
I've found that I usually have to link it up manually.

<a name="submodule" />

[submodule]: #submodule
## Adding Phingistrano to a project as a git submodule ##

    git submodule init
    git submodule add git@github.com:CollegeDegrees/build.git vendor/build

Add the submodule pathing to your .gitmodule file:

    [submodule "vendor/build"]
            path = vendor/build
            url = git@github.com:CollegeDegrees/build.git


### To update the submodule ###

    git submodule update

<a name="build" />

[build]: #build
## Creating a build file ##

The way this repository is used is that it's grafted into your project as a git submodule.
In order to use the features of this project, you'll need to create a phing build.xml in 
your project root directory. This should be named build.xml.

The content of your build.xml should be like this:

    <?xml version="1.0" encoding="UTF-8"?>
    <project name="myproject" default="help">

    </project>

### Importing the build submodule ###

Now that you've got your build file, you need to import the modules. The build submodule 
comes with a main build xml that can be imported if you want to use all the modules. You can 
add it like this (vendor/build is the path of how I set up my submodule):

    <?xml version="1.0" encoding="UTF-8"?>
    <project name="myproject" default="help">

    <!-- Imports -->
    <import file="${project.basedir}/vendor/Phingistrano/build.xml" />
    <import file="${project.basedir}/build.helpers.xml" />

    </project>
    
[I use the helpers file](https://github.com/CodeMeme/Phingistrano/blob/master/build.helpers.example.xml) to keep utility functions that aren't necessarily needed for building the project
If you look at the build.helpers.example.xml file that I included in this repo, you can see that I'm storing 
targets that open a VPN tunnel, restart memcached, perform remote commands, etc...

### Cherrypicking modules ###

If you don't want to use all the modules in this repository, you could cherrypick them into
your main build file by adjusting the path like this:

    <?xml version="1.0" encoding="UTF-8"?>
    <project name="myproject" default="help">

    <!-- Imports -->
    <import file="${project.basedir}/vendor/build/deploy/build.xml" />
    <import file="${project.basedir}/vendor/build/version/build.xml" />
    <import file="${project.basedir}/vendor/build/sniff/build.xml" />

    </project>

**Warning**  
If you cherry pick the modules, you may have to put blank targets from each module in your 
main build file. For example if I used:

    <import file="${project.basedir}/vendor/build/deploy/build.xml" />

deploy has targets which are namespaced like this: deploy.distributed, deploy.prepare, etc...
In my main build file, I would need to create empty targets with those names so that the 
namespace will work. Like this:
    
    <?xml version="1.0" encoding="UTF-8"?>
    <project name="myproject" default="help">

    <!-- Imports -->
    <import file="${project.basedir}/vendor/build/deploy/build.xml" />

    <!-- appendages -->
    <target name="distributed" />
    <target name="prepare" />

    </project> 
This is due to how phing handles namespaced targets.

### required properties ###

This Readme document outlines required properties for each module under heading "The Modules"
For my example, I will show you how you should add the properties to your build file:

    <?xml version="1.0" encoding="UTF-8"?>
    <project name="Phingistrano" default="help">

    <!-- Imports -->
    <import file="${project.basedir}/vendor/Phingistrano/build.xml" />
    <import file="${project.basedir}/build.helpers.xml" />

    <!-- Required properties -->
    <property name="deploy.strategy"   value="hybrid" />
    <property name="deploy.remote"     value="172.99.99.91" />
    <property name="deploy.remotedir"  value="~/${phing.project.name}" />
    <property name="deploy.execline"   value="deploy.production" />
    <property name="deploy.branch"     value="master" />
    <property name="deploy.user"       value="jesse" />
    <property name="deploy.password"   value="jiveturkey" />
    <property name="deploy.path"       value="/var/www/deployments/schools/${deploy.branch}" />
    <property name="deploy.repository" value="git@github.com:CodeMeme/${phing.project.name}.git" />
    <property name="deploy.log"        value="2&gt;&amp;1 | tee ${deploy.path}/deploy.log" />
    <property name="deploy.servers"    value="172.99.99.99, 172.99.99.98" />
    <property name="test.bootstrap"    value="${project.basedir}/tests/TestHelper.php" />
    <property name="test.dir"          value="${project.basedir}/src" />
    <property name="test.incpattern"   value="**/*Test.php" />
    <property name="test.excpattern"   value="" />
    <property name="version.to"        value="jesse@codememe.com" />
    <property name="version.from"      value="robot@codememe.com" />
    <property name="sniff.standard"    value="PEAR" />
    <property name="docs.destdir"      value="${project.basedir}/build/docs" />
    <property name="docs.appdir"       value="${project.basedir}/app" />
    <property name="docs.library"      value="${project.basedir}/src" />

    </project>

### Main Targets ###

In order to do things with Phing, your build.xml will need targets. These are the commands 
that you enter on the command line when you use phing. i.e : $phing [ target ]

    <?xml version="1.0" encoding="UTF-8"?>
    <project name="Phingistrano" default="help">

    <!-- Imports -->
    <import file="${project.basedir}/vendor/Phingistrano/build.xml" />
    <import file="${project.basedir}/build.helpers.xml" />

    <!-- Required properties -->
    <property name="deploy.strategy"   value="hybrid" />
    <property name="deploy.remote"     value="172.99.99.91" />
    <property name="deploy.remotedir"  value="~/${phing.project.name}" />
    <property name="deploy.execline"   value="deploy.production" />
    <property name="deploy.branch"     value="master" />
    <property name="deploy.user"       value="jesse" />
    <property name="deploy.password"   value="jiveturkey" />
    <property name="deploy.path"       value="/var/www/deployments/schools/${deploy.branch}" />
    <property name="deploy.repository" value="git@github.com:CodeMeme/${phing.project.name}.git" />
    <property name="deploy.log"        value="2&gt;&amp;1 | tee ${deploy.path}/deploy.log" />
    <property name="deploy.servers"    value="172.99.99.99, 172.99.99.98" />
    <property name="test.bootstrap"    value="${project.basedir}/tests/TestHelper.php" />
    <property name="test.dir"          value="${project.basedir}/src" />
    <property name="test.incpattern"   value="**/*Test.php" />
    <property name="test.excpattern"   value="" />
    <property name="version.to"        value="jesse@codememe.com" />
    <property name="version.from"      value="robot@codememe.com" />
    <property name="sniff.standard"    value="PEAR" />
    <property name="docs.destdir"      value="${project.basedir}/build/docs" />
    <property name="docs.appdir"       value="${project.basedir}/app" />
    <property name="docs.library"      value="${project.basedir}/src" />

    <!-- Main Targets -->
    <target name="help"
            depends="modules.help"
            description="This help Menu." />

    <target name="build"
            depends="test.do, sniff.do, docs.do"
            description="Main Build Routine." />

    <target name="release"
            depends="version.tag, deploy.production, version.notify"
            description="Executes a release to production." />

    <target name="deploy.production"
            depends="deploy.do"
            description="Deploys master branch to production." />

    <target name="rollback.production"
            depends="rollback.do"
            description="Rolls back a production release." />


    </project>

### Targets that assign properties ###

At my work, we have multiple environments and repositories in which we use throught an application 
development cycle. In order to build different repositories on different environments we use 
targets that assign properties. For example:

    <!-- Targets that assign properties -->
    <target name="staging.properties"      depends="deploy.currentbranch" >
        <property name="deploy.servers"    value="172.99.99.97"    override="true" />
        <property name="deploy.path"       value="/var/www/deployments/phingistrano/develop" override="true" />
        <property name="deploy.log"        value="2&gt;&amp;1 | tee -a ${deploy.path}/deploy.log" override="true" />
        <property name="deploy.execline"   value="deploy.staging" override="true" />
    </target>

    <target name="testing.properties"      depends="deploy.currentbranch">
        <property name="deploy.path"       value="/var/www/deployments/phingistrano/develop"   override="true" />
        <property name="deploy.log"        value="2&gt;&amp;1 | tee -a ${deploy.path}/deploy.log" override="true" />
        <property name="deploy.execline"   value="deploy.testing" override="true" />
    </target>

Then I add new targets under main targets that incorporate these property adding targets
as a dependency:

    <target name="deploy.staging"
            depends="staging.properties, deploy.do"
            description="Deploys the current branch to staging." />

    <target name="deploy.testing"
            depends="testing.properties, rollback.do"
            description="Deploys master branch to production." />

    <target name="rollback.staging"
            depends="staging.properties, rollback.do"
            description="Rolls back the staging environment." />

    <target name="rollback.testing"
            depends="testing.properties, rollback.do"
            description="Rolls back the testing environment." />

### Example build.xml ###

[my final build.xml](https://github.com/CodeMeme/Phingistrano/blob/master/build.example.xml) looks like this:

    <?xml version="1.0" encoding="UTF-8"?>
    <project name="Phingistrano" default="help">

    <!-- Imports -->
    <import file="${project.basedir}/vendor/Phingistrano/build.xml" />
    <import file="${project.basedir}/build.helpers.xml" />

    <!-- Required properties -->
    <property name="deploy.strategy"   value="hybrid" />
    <property name="deploy.remote"     value="172.99.99.91" />
    <property name="deploy.remotedir"  value="~/${phing.project.name}" />
    <property name="deploy.execline"   value="deploy.production" />
    <property name="deploy.branch"     value="master" />
    <property name="deploy.user"       value="jesse" />
    <property name="deploy.password"   value="jiveturkey" />
    <property name="deploy.path"       value="/var/www/deployments/schools/${deploy.branch}" />
    <property name="deploy.repository" value="git@github.com:CodeMeme/${phing.project.name}.git" />
    <property name="deploy.log"        value="2&gt;&amp;1 | tee ${deploy.path}/deploy.log" />
    <property name="deploy.servers"    value="172.99.99.99, 172.99.99.98" />
    <property name="test.bootstrap"    value="${project.basedir}/tests/TestHelper.php" />
    <property name="test.dir"          value="${project.basedir}/src" />
    <property name="test.incpattern"   value="**/*Test.php" />
    <property name="test.excpattern"   value="" />
    <property name="version.to"        value="jesse@codememe.com" />
    <property name="version.from"      value="robot@codememe.com" />
    <property name="sniff.standard"    value="PEAR" />
    <property name="docs.destdir"      value="${project.basedir}/build/docs" />
    <property name="docs.appdir"       value="${project.basedir}/app" />
    <property name="docs.library"      value="${project.basedir}/src" />   

    <!-- Main Targets -->
    <target name="help"
            depends="modules.help"
            description="This help Menu." />

    <target name="build"
            depends="test.do, sniff.do, docs.do"
            description="Main Build Routine." />

    <target name="release"
            depends="version.tag, deploy.production, version.notify"
            description="Executes a release to production." />

    <target name="deploy.production"
            depends="deploy.do"
            description="Deploys master branch to production." />

    <target name="rollback.production"
            depends="rollback.do"
            description="Rolls back a production release." />

    <target name="deploy.staging"
            depends="staging.properties, deploy.do"
            description="Deploys the current branch to staging." />

    <target name="rollback.staging"
            depends="staging.properties, rollback.do"
            description="Rolls back the staging environment." />

    <target name="deploy.testing"
            depends="testing.properties, deploy.do"
            description="Deploys current branch to testing." />

    <target name="rollback.testing"
            depends="testing.properties, rollback.do"
            description="Rolls back the testing environment." />

    <!-- Targets that assign properties -->
    <target name="staging.properties"      depends="deploy.currentbranch" >
        <property name="deploy.servers"    value="172.99.99.97"    override="true" />
        <property name="deploy.path"       value="/var/www/deployments/phingistrano/develop" override="true" />
        <property name="deploy.log"        value="2&gt;&amp;1 | tee -a ${deploy.path}/deploy.log" override="true" />
        <property name="deploy.execline"   value="deploy.staging" override="true" />
    </target>

    <target name="testing.properties"      depends="deploy.currentbranch">
        <property name="deploy.path"       value="/var/www/deployments/phingistrano/develop"   override="true" />
        <property name="deploy.log"        value="2&gt;&amp;1 | tee -a ${deploy.path}/deploy.log" override="true" />
        <property name="deploy.execline"   value="deploy.testing" override="true" />
    </target>
    </project>

<a name="modules" />

[modules]: #modules
## The Modules ##

For each module that you use, there are certain properties that may be required. 
In your main build file, you should control the modules with these properties. I will 
attempt to outline what the properties are and what they do.  

<a name="deploy" />

[deploy]: #deploy
### Deploy ###

* phing deploy.staging { deploys the users current branch to staging }
* phing deploy.production { deploys master branch to production (always master) }
* phing deploy.test { deploys the users current branch to test }

#### Deploy properties ####

##### deploy.branch #####

deploy branch is the branch of your repository that will be used in the deployment
the default value is "master"  

    <property name="deploy.branch"     value="master" />

##### deploy.user & deploy.password #####

This is the user name and password of the ssh account that will be used during deployment.
  
    <property name="deploy.user"       value="myUser" />
    <property name="deploy.password"   value="myPass" />

##### deploy.path #####

This is the path on the deployment server where your "releases" directory resides  

    <property name="deploy.path"       value="/var/www/deployments/application/${deploy.branch}" />

##### deploy.repository #####

This property holds the address of your git repository.  

    <property name="deploy.repository" value="git@github.com:myGithub/${phing.project.name}.git" />

##### deploy.log #####

This is not the file name of your log. This is the logging directive that you can append to 
remote shell commands to manage the logging of your deployment. By default the deploy.log 
value is "2>&1 tee /your/deploy/path/deploy.log". This will make the output get sent to the 
terminal, but also get logged in the file: "/your/deploy/path/deploy.log"  

    <property name="deploy.log"        value="2&gt;&amp;1 | tee ${deploy.path}/deploy.log" />

##### deploy.servers #####

This is a comma delimited list of the server IPs or host names in which you will deploy your build. 
Make sure that you can ssh to these servers and that the deploy.user is capable of logging in and 
writing to the deploy.path on all of these servers. If there is only 1 deployment server simply
write the one IP or hostname.  

    <property name="deploy.servers"    value="172.99.99.99, 172.98.98.98" />

#### Defining a deployment strategy ####

To define a deployment strategy, enter it's name for the value of the deploy.strategy property. 
A deployment strategy must be defined but distributed will be defined by default.  

    <property name="deploy.strategy"   value="distributed" />
    <property name="deploy.strategy"   value="direct" />
    <property name="deploy.strategy"   value="hybrid" />

#### Deploy strategies ####

Each different kind of deployment is called a strategy.
There are now 3 different strategies available for deployment

##### Distributed #####

This is the most basic strategy. This downloads or updates your git repository locally and creates a
deployment tarball. The deployment tarball is uploaded to your designated deployment servers. 

##### Direct #####

This strategy uses the least bandwidth. This downloads or updates your git respository 
directly on the deployment servers. A tarball is still created but is not uploaded. The cached 
copy is formatted and moved in place to your deployment directory.

##### Hybrid #####

This strategy uses elements of both direct and distributed. This downloads or updates your git 
repository to a remote server, and from the remote server it runs a distributed strategy to 
distribute your build to the deployment servers. This strategy requires a remote server and 
requires that the remote server has phing and all the necessary dependencies for your build
routine.

hybrid needs the following properties in addition to the rest of the deploy properties  

    <property name="deploy.remote"     value="172.97.97.97" />
    <property name="deploy.remotedir"  value="~" />
    <property name="deploy.execline"   value="deploy.production" />

<a name="rollback" /> 

[rollback]: #rollback
### Rollback ###

#### Command line usage ####

* phing rollback.staging { rollsback to the most recent deployment before current}
* phing rollback.production -Drollback.direction=forward { rolls production forward (reverse is default) }
* phing rollback.getlist { displays a list of available deployments ranging from most recent to oldest identify the list as numbers 1-5 or whatever }
* phing rollback.test -Drollback.selected=[ #number 1-5 ] { selects a specific deployment from the list (if less than 2 it will be 1, if more than max it will be max) }

<a name="test" />

[test]: #test
### Test ###

#### test.bootstrap ####

This property value will refer to a bootstrap file that your phpunit execution will rely on. 
If the file doesn't exist then the bootstrap will be created automatically, so don't worry 
about it if your project unit tests don't require bootstrapping. As long as this property is 
defind and phing can write to the directory, this bootstrap file will be created with an empty 
directive.  

    <property name="test.bootstrap"    value="${project.basedir}/tests/TestHelper.php" /> 

#### test.dir ####

Will assign a directory for php unit to traverse looking for unit tests.
At this time only one directory is supported. Set it to a higher directory if you can use multiple directories recursively.

    <property name="test.dir"          value="${project.basedir}/src" />

#### test.incpattern ####

The pattern of files to include when looking for unit tests. Defaults to the value in the example.
At this time only one line of inclusion is supported.

    <property name="test.incpattern"   value="**/*Test.php" />

#### test.excpattern ####

The pattern of files to exclude when looking for unit tests.
At this time only one line of exclusion is supported.

    <property name="test.excpattern"   value="" /> 

<a name="version" />

[version]: #version
### Version ###

#### version.to and version.from ####

The version.to property is the email address which the notification will be sent to on a new version.  
version.from is the email address of who or what the mail will be sent from.   

    <property name="version.to"        value="you@yourdomain.com" />
    <property name="version.from"      value="build-robot@yourdomain.com" />  

<a name="sniff" />

[sniff]: #sniff
### Sniff ###

#### sniff.standard ####
 
This will set the sniff standard for which PHP Codesniffer will use when it makes a pass over
your code. This can be one of the commonly used standards like PEAR or Zend or it can be the  
path to a custom ruleset.xml file.  

    <property name="sniff.standard"    value="${project.basedir}/library/MyLib" />  

<a name="docs" />

[docs]: #docs
## Docs ###

#### docs.destdir ####

This is the directory that your docs will be generated in.

    <property name="docs.destdir"       value="${project.basedir}/build/docs" /> 

#### docs.library ####

This should be a path to a library folder that you may want the documentor to make a pass over
    
    <property name="docs.library"      value="${project.basedir}/src" />
    
#### docs.appdir ####

This should be a path to your application directory

    <property name="docs.appdir"       value="${project.basedir}/app" />


<a name="gotchas" />
 
[gotchas]: #gotchas
##Gotchas ##
  
### namespaced submodules won't work if a base target doesn't exist ###

I percieve this as a problem with phing. If you refer to an Imported target (namespaced target i.e.  namespaced.mytarget) in "depends", in the `<phing />` task or even from the command line, if mytarget doesn't exist, then  mytarget from the imported module fills it in, and won't be availalbe at namespaced.mytarget. It's a confusing problem but suffice it to say that the easiest way to work around this is to createempty targets for submodule targets. 

due to this percieved problem, you have to have an empty target in your main build file that corresponds to your submodule target. This can be as simple as creating a target with the appropriate name:

    <target name="mytarget" />  

A ticket/CR has been submitted to phing.tigris to have this fixed. The ticket # is 620.

<a name="about" />

[about]: #about
## About ##

This code was developed by Codememe. Codememe is:  

* *Eric Clemmons*
* *Evan Kaufman*
* *Jesse Greathouse*
   
Special thanks to these technologies and those who product them:  

* Phing
* Git
* Github
* PHPDocumentor
* PHPUnit
* PHP Codesniffer
* SSH2
* PHP
