# Phingistrano ![project status](http://stillmaintained.com/CodeMeme/Phingistrano.png) #
A PHP utility for building and deploying projects based on Phing and other paralell technology.

## NEW! WINNT deployment is now possible ##
Ive added a new deployment property called deploy.arch. By default it will have the value of UNIX, but if you change the value to WINNT then it will change the value of the directory separators in commands where windows architecture is relevant. Due to the difficulty in symlinking windows folders with MKLINK, the deployment procedure is slightly different in that the current deployment is a physical copy instead of a symlink. This was due to some problems with OpenSSH and Cygwin which was making symlinking too difficult.

## NEW! phpseclib library ##
Ive added the ability to use a "Pure PHP" implementation of ssh2 thanks to the PEAR seclib library. For those who have had trouble with using Phingistrano because of the SSH2 dependency, you may want to have a look.    
[README][phpseclib]

## Table of Contents ##
* [Overview][overview]
* [Command line usage][commandline]
* [Installing Phing and dependencies][dependencies]
* [Adding Phingistrano to a project as a git submodule][submodule]
* [Creating a build file][build]
* [Symfony2 Helpers][symfony2]
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

Phingistrano tries to be flexible by loading it's individual components as modules by the Phing *importTask* task. This repository does not contain the main build.xml to use, but it does give an [example of what one might look like](https://github.com/CodeMeme/Phingistrano/blob/master/phingistrano/build.example.xml) .  

It's important to understand that by this paradigm, you must tailor your buid.xml to the project, and import these submodules as needed. If you need customized versions of individual components, you could simply override or use different tasks defined in your build.xml, or even better, [import a custom build.helpers.xml](https://github.com/CodeMeme/Phingistrano/blob/master/phingistrano/build.helpers.example.xml) . In the examples I've shown, the targets are just aggregated targets from within the modules, strung together with the *depends* attribute. If you look at how it's done, you will likely see how easy, clean and flexible this is.  

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

<a name="phpseclib" />

## SSH2 PHP Extension *OR* phpseclib Net_SSH2 ##
Since the PHP ssh2 extension has been difficult to install, and for some people impossible to extend php due to system permissions, You can use this library, PHPs SSH2 extension or both.

[phpseclib]: #phpseclib
### phpseclib Net_SSH2 and NetSFTP ###

Ive tailored a custom ssh task for Phingistrano that will also use the Net_SSH2 and Net_SFTP classes from the [PEAR phpseclib library](http://phpseclib.sourceforge.net/documentation/net.html#net_ssh_dependencies). 

The one drawback that I've found about this library is that uploads can be *VERY* slow. If the upload speed becomes a problem for you, then I suggest looking into the direct strategy for deployment as it requires no uploading.

You can install the [phpseclib](http://phpseclib.sourceforge.net/pear.htm) dependencies with the following commands:

    sudo pear channel-discover phpseclib.sourceforge.net
    sudo pear install phpseclib/Net_SSH2
    sudo pear install phpseclib/Net_SFTP
    sudo pear install phpseclib/Math_BigInteger
    sudo pear install phpseclib/Crypt_Random
    sudo pear install phpseclib/Crypt_Hash
    sudo pear install phpseclib/Crypt_TripleDES
    sudo pear install phpseclib/Crypt_RC4
    sudo pear install phpseclib/Crypt_AES
    sudo pear install phpseclib/Crypt_RSA

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

    git submodule add git@github.com:CodeMeme/Phingistrano.git vendor/Phingistrano
    git submodule init

This should automatically create a .gitmodules file for you. If not, you can create
one manually with the following contents:

    [submodule "vendor/Phingistrano"]
            path = vendor/Phingistrano
            url = git@github.com:CodeMeme/Phingistrano.git

### To update the submodule ###

    git submodule update

(You should run this once upon sub-repository creation as well.)

<a name="build" />

[build]: #build
## Creating a build file ##

The way this repository is used is that it's grafted into your project as a git submodule.
In order to use the features of this project, you'll need to create a phing build.xml in 
your project root directory. This should be named build.xml.

The content of your build.xml should be like this:

    <?xml version="1.0" encoding="UTF-8"?>
    <project name="myproject" default="help">
    
    <!-- Required properties -->
    <property name="phingistrano.dir"  value="${project.basedir}/vendor/Phingistrano" />
    <property name="build.target"      value=".build" />

    </project>
    
The path to Phingistrano will vary depending on your installation. Possibly you could even add the Phingistrano library by pear, or however you want.

### required properties ###

This Readme document outlines required properties for each module under heading "The Modules" For my example, I will show you how you should add the properties to your build file:

    <?xml version="1.0" encoding="UTF-8"?>
    <project name="Phingistrano" default="help">

    <!-- Required properties -->
    <property name="phingistrano.dir"  value="${project.basedir}/vendor/Phingistrano" />
    <property name="build.target"      value=".build" />
    <property name="deploy.user"       value="jesse" />
    <property name="deploy.password"   value="jiveturkey" />
    <property name="deploy.path"       value="/var/www/deployments/${phing.project.name}/${deploy.branch}" />
    <property name="deploy.repository" value="git@github.com:CodeMeme/${phing.project.name}.git" />
    <property name="deploy.servers"    value="172.99.99.99, 172.99.99.98" />
    <property name="version.to"        value="jesse@codememe.com" />
    <property name="version.from"      value="robot@codememe.com" />

    </project>
    
### Optional Properties ###

This is a list of properties available for the individual modules but not needed for successful execution. You can use these properties to configure your deployment, or leave them out if you accept the defaults.

    <!-- available properties (not required) -->
        <!-- deploy common -->
        <property name="deploy.branch"      value="master" />
        <property name="deploy.backissue"   value="5" />
        <property name="tunnel.configured"  value="false" />

        <!-- deploy direct -->
        <property name="deploy.log"        value="2&gt;&amp;1 | tee ${deploy.path}/deploy.log" />
        
        <!-- test phpunit -->
        <property name="test.bootstrap"    value="${build.target}/tests/bootstrap.php" />
        <property name="test.dir"          value="${project.basedir}" />
        <property name="test.incpattern"   value="**/*Test.php" />
        <property name="test.excpattern"   value="" />
        <property name="test.type"         value="xml" />
        <property name="test.usefile"      value="true" />
        <property name="test.haltfail"     value="true" />
        <property name="test.halterror"    value="true" />
        
        <!-- sniff phpcs -->
        <property name="sniff.standard"         value="PEAR" />
        <property name="sniff.ignorepatterns"   value="${build.target},vendor,Sniff.php" />
        <property name="sniff.show"             value="true" />
        
        <!-- docs phpdocumentor -->
        <property name="docs.destdir"       value="${build.target}/docs" />
        <property name="docs.target"        value="${project.basedir}" />
        <property name="docs.ignore"        value="" />
        <property name="docs.output"        value="HTML:frames:DOM/earthli" />

### Importing the build submodule ###

Now that you've got your build file, you need to import the modules. The build submodule 
comes with a main build xml that can be imported if you want to use all the modules. Imports should always be underneath the predefined properties to ensure that the properties in the modules can be overridden by your preferences. You can 
add it like this (vendor/Phingistrano is the path of how I set up my submodule):

    <?xml version="1.0" encoding="UTF-8"?>
    <project name="myproject" default="help">
    
    <!-- Required properties -->
    <property name="phingistrano.dir"  value="${project.basedir}/vendor/Phingistrano" />
    <property name="build.target"      value=".build" />
    <property name="deploy.user"       value="jesse" />
    <property name="deploy.password"   value="jiveturkey" />
    <property name="deploy.path"       value="/var/www/deployments/${phing.project.name}/${deploy.branch}" />
    <property name="deploy.repository" value="git@github.com:CodeMeme/${phing.project.name}.git" />
    <property name="deploy.servers"    value="172.99.99.99, 172.99.99.98" />
    <property name="version.to"        value="jesse@codememe.com" />
    <property name="version.from"      value="robot@codememe.com" />

    <!-- Imports -->
    <import file="${project.basedir}/build.helpers.xml" />
    <import file="${project.basedir}/vendor/Phingistrano/build.xml" />

    </project>
    
[I use the helpers file](https://github.com/CodeMeme/Phingistrano/blob/master/build.helpers.example.xml) to keep utility functions that aren't necessarily needed for building the project
If you look at the build.helpers.example.xml file that I included in this repo, you can see that I'm storing 
targets that open a VPN tunnel, restart memcached, perform remote commands, etc...

### Cherrypicking modules ###

If you don't want to use all the modules in this repository, you could cherrypick them into
your main build file by adjusting the path like this:

    <?xml version="1.0" encoding="UTF-8"?>
    <project name="myproject" default="help">
    
    <!-- Required properties -->
    <property name="phingistrano.dir"  value="${project.basedir}/vendor/Phingistrano" />
    <property name="build.target"      value=".build" />
    <property name="deploy.user"       value="jesse" />
    <property name="deploy.password"   value="jiveturkey" />
    <property name="deploy.path"       value="/var/www/deployments/${phing.project.name}/${deploy.branch}" />
    <property name="deploy.repository" value="git@github.com:CodeMeme/${phing.project.name}.git" />
    <property name="deploy.servers"    value="172.99.99.99, 172.99.99.98" />
    <property name="version.to"        value="jesse@codememe.com" />
    <property name="version.from"      value="robot@codememe.com" />

    <!-- Imports -->
    <import file="${project.basedir}/vendor/Phingistrano/deploy/build.xml" />
    <import file="${project.basedir}/vendor/Phingistrano/version/build.xml" />
    <import file="${project.basedir}/vendor/Phingistrano/sniff/build.xml" />

    </project>

**Warning**  
If you cherry pick the modules, you may have to put blank targets from each module in your 
main build file. For example if I used:

    <import file="${project.basedir}/vendor/Phingistrano/deploy/build.xml" />

deploy has targets which are namespaced like this: deploy.distributed, deploy.prepare, etc...
In my main build file, I would need to create empty targets with those names so that the 
namespace will work. Like this:
    
    <?xml version="1.0" encoding="UTF-8"?>
    <project name="myproject" default="help">

    <!-- Imports -->
    <import file="${project.basedir}/vendor/Phingistrano/deploy/build.xml" />

    <!-- appendages -->
    <target name="distributed" />
    <target name="prepare" />

    </project> 
This is due to how phing handles namespaced targets.



### Main Targets ###

In order to do things with Phing, your build.xml will need targets. These are the commands 
that you enter on the command line when you use phing. i.e : $phing [ target ]

    <?xml version="1.0" encoding="UTF-8"?>
    <project name="Phingistrano" default="help">

    <!-- Required properties -->
    <property name="phingistrano.dir"  value="${project.basedir}/vendor/Phingistrano" />
    <property name="build.target"      value=".build" />
    <property name="deploy.user"       value="jesse" />
    <property name="deploy.password"   value="jiveturkey" />
    <property name="deploy.path"       value="/var/www/deployments/${phing.project.name}/${deploy.branch}" />
    <property name="deploy.repository" value="git@github.com:CodeMeme/${phing.project.name}.git" />
    <property name="deploy.servers"    value="172.99.99.99, 172.99.99.98" />
    <property name="version.to"        value="jesse@codememe.com" />
    <property name="version.from"      value="robot@codememe.com" />
    
    <!-- Imports -->
    <import file="${project.basedir}/build.helpers.xml" />
    <import file="${project.basedir}/vendor/Phingistrano/build.xml" />

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

    <!-- Required properties -->
    <property name="phingistrano.dir"  value="${project.basedir}/vendor/Phingistrano" />
    <property name="build.target"      value=".build" />
    <property name="deploy.user"       value="jesse" />
    <property name="deploy.password"   value="jiveturkey" />
    <property name="deploy.path"       value="/var/www/deployments/${phing.project.name}/${deploy.branch}" />
    <property name="deploy.repository" value="git@github.com:CodeMeme/${phing.project.name}.git" />
    <property name="deploy.servers"    value="172.99.99.99, 172.99.99.98" />
    <property name="version.to"        value="jesse@codememe.com" />
    <property name="version.from"      value="robot@codememe.com" />
    
    <!-- Imports -->
    <import file="${project.basedir}/build.helpers.xml" />
    <import file="${project.basedir}/vendor/Phingistrano/build.xml" />   

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

#### Global properties ####

    <!-- required properties -->
    <property name="phingistrano.dir"  value="${project.basedir}/vendor/Phingistrano" />
    <property name="build.target"      value=".build" />
    
##### phingistrano.dir #####

This is the path to the location of your Phingistrano libraries. This is important due to how the modules work. With this value set, you can use one central phingistrano repository for all of your projects, possibly even install it via a Pear channel, or just keep the library in any place of your choosing. It is required, however, to set this value because modules no longer self resolve.  

    <property name="phingistrano.dir"  value="${project.basedir}/vendor/Phingistrano" />

##### build.target #####

This is the folder that your build related media will appear. By default it creates and uses the folder: .build  

    <property name="build.target"      value=".build" />

<a name="deploy" />

[deploy]: #deploy
### Deploy ###

* phing deploy.staging { deploys the users current branch to staging }
* phing deploy.production { deploys master branch to production (always master) }
* phing deploy.test { deploys the users current branch to test }

#### Deploy Hooks ####

##### postcache #####

Runs a target called "postcache" immediately as the cacheing is completed and before it wraps it into a tarball. This is useful in situations where you may need to run treatment scripts like the vendors script in Symfony2:

    <!-- postcache -->
    <target name="postcache"
            description="Refreshes the vendors" >
            <exec dir="${project.basedir}/${build.target}/cached-copy/app"
                outputProperty="targets"
                command="bin/vendors --install" />
    </target>

##### precache #####

Runs a target called "precache" right before you enter in to the repository cacheing part of deploy. This is useful if there are certain treatments, specific to the project, that you need to run before the repository gets cached

#### Deploy properties ####

    <!-- deploy properties -->
    <!-- required -->
    <property name="deploy.user"       value="jesse" />
    <property name="deploy.password"   value="jiveturkey" />
    <property name="deploy.path"       value="/var/www/deployments/${phing.project.name}/${deploy.branch}" />
    <property name="deploy.repository" value="git@github.com:CodeMeme/${phing.project.name}.git" />
    <property name="deploy.servers"    value="172.99.99.99, 172.99.99.98" />
    
    <!-- optional -->
    <property name="deploy.strategy"     value="distributed" />
    <property name="deploy.history"      value="5" />
    <property name="deploy.branch"       value="master" />
    <property name="deploy.log"          value="2&gt;&amp;1 | tee ${deploy.path}/deploy.log" />
    
##### deploy.history #####

By default, Phingistrano will keep every deployment if your project. If you do not want to limit the amount of back deployments simply do not set this value. If you would like to limit the number of back deployments, that Phingistrano will retain, you can do that with the deploy.history property. If you do not want to keep any back deployments, simply set the value to zero (not recommended). 

    <property name="deploy.history"    value="5" />
    
##### deploy.arch #####

deploy architecture of the deployment system. By default the value is "UNIX" but can be set to "WINNT" for deploying to windows architectures. This is only supported for Windows servers running OpenSSH with Cygwin. 

**ALERT**
Setting the deploy.arch property to WINNT will change the deployment procedure in that the current deployment will be a physical copy instead of a symlink. there will still be a backlog of releases in the releases folder, the only thing that changes is that the symlink will be a physical copy of the specified release folder.  
    
    <property name="deploy.arch"       value="UNIX" />

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

#### Deploy strategies ####

There are two different deployment strategies: distributed and direct.

##### Distributed #####

This is the most basic strategy. This downloads or updates your git repository locally and creates a
deployment tarball. The deployment tarball is uploaded to your designated deployment servers. 

##### Direct #####

This strategy uses very little bandwidth (good for large projects that take forever to upload). This downloads or updates your git respository 
directly on the deployment servers. A tarball is still created but is not uploaded. The cached 
copy is formatted and moved in place to your deployment directory.

##### Hybrid #####

Hybrid strategy has been depreciated. It was too complicated to set up and had problems. If you are trying to run a hybrid strategy, the easiest way is to set up a distributed strategy on a remote server, and just trigger the phing target with a remote command in your local helpers file.

[rollback]: #rollback
### Rollback ###

    <!-- rollback properties -->
    <property name="rollback.direction" value="reverse"/>
    <property name="rollback.depth"     value="1" />
    <property name="rollback.selected"  value="false" />

#### Command line usage ####

* phing rollback.staging { rollsback to the most recent deployment before current}
* phing rollback.production -Drollback.direction=forward { rolls production forward (reverse is default) }
* phing rollback.getlist { displays a list of available deployments ranging from most recent to oldest identify the list as numbers 1-5 or whatever }
* phing rollback.test -Drollback.selected=[ #number 1-5 ] { selects a specific deployment from the list (if less than 2 it will be 1, if more than max it will be max) }

<a name="test" />

[test]: #test
### Test ###
A result file of your unit testing will be found in the reports subdirectory of your build target directory. By default: /.build/reports/testsuites.xml

    <!-- unit test properties -->
    <property name="test.bootstrap"    value="${build.target}/tests/bootstrap.php" />
    <property name="test.dir"          value="${project.basedir}" />
    <property name="test.incpattern"   value="**/*Test.php" />
    <property name="test.excpattern"   value="" />
    <property name="test.type"         value="xml" />
    <property name="test.outfile"      value="testsuites.xml" />
    <property name="test.haltfail"     value="true" />
    <property name="test.halterror"    value="true" />

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
    
#### test.type ####

The format of the output from testing, can be xml, plain, clover, or brief.

    <property name="test.type"   value="xml" />
    
#### test.outfile ####

The format of the the name of the output file from the testing result. The default is testsuites.xml.

    <property name="test.outfile"   value="testsuites.xml" />

<a name="version" />

[version]: #version
### Version ###

    <!-- version properties -->
    <!-- required -->
    <property name="version.to"        value="jesse@codememe.com" />
    <property name="version.from"      value="robot@codememe.com" />

#### version.to and version.from ####

The version.to property is the email address which the notification will be sent to on a new version.  
version.from is the email address of who or what the mail will be sent from.   

    <property name="version.to"        value="you@yourdomain.com" />
    <property name="version.from"      value="build-robot@yourdomain.com" />  

<a name="sniff" />

[sniff]: #sniff
### Sniff ###

    <!-- code sniff properties -->
    <property name="sniff.standard"         value="PEAR" />
    <property name="sniff.ignorepatterns"   value="${build.target},vendor,Sniff.php" />
    <property name="sniff.show"             value="true" />

#### sniff.standard ####
 
This will set the sniff standard for which PHP Codesniffer will use when it makes a pass over
your code. This can be one of the commonly used standards like PEAR or Zend or it can be the  
path to a custom ruleset.xml file.  

    <property name="sniff.standard"    value="${project.basedir}/library/MyLib" />  

<a name="docs" />

[docs]: #docs
## Docs ###

    <!-- set default documentor properties -->
    <property name="docs.destdir"      value="${build.target}/docs" />
    <property name="docs.target"       value="${project.basedir}" />
    <property name="docs.ignore"       value="" />
    <property name="docs.output"       value="HTML:frames:DOM/earthli" />

#### docs.destdir ####

This is the directory that your docs will be generated in.

    <property name="docs.destdir"       value="${project.basedir}/build/docs" /> 

#### docs.target ####

This should be a path to a folder that you may want the documentor to make a pass over
    
    <property name="docs.target"       value="${project.basedir}" />
    
#### docs.output ####

PHPDocumentor output format 

    <property name="docs.output"       value="HTML:frames:DOM/earthli" />
    
<a name="symfony2" />

[symfony2]: #symfony2
## Symfony2 Helpers ##

Symfony2 is a great new php framework but during deployment, some complexity is added to make sure your application gets its needed dependencies and that the application is conditioned for perfect operation. Things like assetic, doctrine migrations, and caching may need some help. I've prepared this example file of how you can do the  conditioning to your symfony 2 app during automated deployment.

### Add the Symfony2 helpers file to your main buld.xml by importing it just like a regular helpers file ###

    <?xml version="1.0" encoding="UTF-8"?>
    <project name="myproject" default="help">

    <!-- Imports -->
    <import file="${project.basedir}/build.helpers.xml" />
    <import file="${project.basedir}/symfony2.helpers.xml" />
    <import file="${project.basedir}/vendor/Phingistrano/build.xml" />

### add the post_deploy routine to your deployment targets ###

    <target name="deploy.production"
            depends="deploy.do, post_deploy"
            description="Deploys master branch to production." />

    <target name="deploy.staging"
            depends="staging.properties, deploy.do, post_deploy.staging"
            description="Deploys the current branch to staging." />
            
### inside the symfony2.helpers.xml ###

#### Assigning symfony properties ####
You will need to assign a property of the symfony environment. You may even need to assign other properties depending on what you're doing. This should help you along in the process.

    <!-- Required properties -->
    <property name="symfony.env" value="prod" />

    <!-- Targets that assign properties -->
    <target name="symfony.stage.properties" >
        <property name="symfony.env" value="stage" override="true" />
    </target>

    <target name="symfony.test.properties" >
        <property name="symfony.env" value="test" override="true" />
    </target>
    
##### Asserting properties for the right environment #####
Now when you need to augment the targets to perform the right environmental context, you just add the symfony.properties.stage, symfony.properties.test target to the depends attribute. This is exactly how Phingistrano does it in the main build paradigm.

#### postcache ####
The symfony2 helpers file takes advantage of the new postcache hook in Phingistrano. "postcache" is used immediately after the deploy module creates it's "cached-copy". Running these targets in "postcache" ensures that you will have refreshed versions of your vendors folder once it creates the tarball for distribution.

    <!-- postcache -->
    <target name="postcache"
        depends="refresh_vendors"
        description="postcache deploy hook" />

    <!-- refresh vendors -->
    <target name="refresh_vendors"
        description="Refreshes the vendors" >
        <exec dir="${project.basedir}/${build.target}/cached-copy"
              passthru="true"
              command="rm -rf vendor/ &amp;&amp; bin/vendors install" />
    </target>

#### postdeploy ####
These are targets that will be run immediately after your deployment on all the servers defined for this environment. The best way to manage this "postdeploy" sequence is to aggregate all of your targets into the "depends" attribute.

In this example I've created postdeploy targets for each additional environment context. This ensures that your properties get assigned based on what environment you're deploying to.

    <!-- postdeploy targets -->
    <target name="post_deploy"
            depends="migrate, 
                     assetic_dump, 
                     clear_cache" 
            description="Execute post deployment utilities on production" />

    <target name="post_deploy.staging"
            depends="staging.properties,
                     symfony.stage.properties,
                     post_deploy" 
            description="Execute post deployment utilities on staging" />

    <target name="post_deploy.testing"
        depends="staging.properties,
                 symfony.test.properties,
                 post_deploy" 
        description="Execute post deployment utilities on testing" />

#### migrate ####

This performs doctrine migrations on your application

    <!-- doctrine:migrations:migrate -->
    <target name="migrate"
            description="Run migrations on production servers" >
            <property name="command" 
                value="(
                cd ${deploy.path}/current/app &amp;&amp; 
                ./console --no-ansi --env=${symfony.env} doctrine:migrations:migrate  
                )"
                override="true" />
            <foreach  list="${deploy.servers}" 
                param="deploy.server" 
                target="deploy.remotecmd" />
    </target>
    
#### assetic_dump ####

This installs assets and performs an assetic dump. Bear in mind that symlinked assets in your tarball will have incorrect paths on your deployment server unless you install the assets on post deployment. This is because symfony uses only absolute paths in the asset install and has no option for relative paths.

    <!-- assets:install -->
    <!-- assetic:dump -->
    <target name="assetic_dump"
            description="Warm assets on production servers" >
            <property name="command" 
                value="( 
                cd ${deploy.path}/current/app &amp;&amp; 
                ./console --no-ansi --env=${symfony.env} --symlink assets:install ../web &amp;&amp;
                ./console --no-ansi --env=${symfony.env} assetic:dump  
                )" 
                override="true" />
            <foreach list="${deploy.servers}" 
                param="deploy.server" 
                target="deploy.remotecmd" />
    </target>

#### clear_cache ####

This clears the cache, warms up the cache, and performs a cruicial directory permissions adjustment so that the webserver can read/write to your cache. This example assumes that the webserver user is in the same group as your directory owner/deployment user. Individual milage may vary depending on how your hosting environment is set up.

    <!-- cache:clear -->
    <!-- cache:warmup -->
    <!-- fix cache permissions assumes that the webserver is in the same group as the owner -->
    <target name="clear_cache"
            description="dump and warm cache on production servers" >
            <property name="command" 
                value="(
                cd ${deploy.path}/current/app &amp;&amp; 
                ./console cache:clear --no-warmup &amp;&amp; 
                ./console cache:warmup --no-ansi &amp;&amp; 
                chmod -R 770 cache/
                )" 
                override="true" />
            <foreach list="${deploy.servers}" 
                     param="deploy.server" 
                     target="deploy.remotecmd" />
    </target>


<a name="gotchas" />
 
[gotchas]: #gotchas
##Gotchas ##
  
### namespaced submodules won't work if a base target doesn't exist ###

With phing version 2.4, you have to have an empty target in your main build file that corresponds to your submodule target. This can be as simple as creating a target with the appropriate name:

    <target name="mytarget" />  

The ticket #620 has been completed by Matthias from Phing development and starting on Phing version 2.5 these empty targets will not be necessary.


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
