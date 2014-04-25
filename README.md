# Build Process for the PHPUnit Website

We use [Bower](http://bower.io/) to manage the dependencies of the PHPUnit website and [Grunt](http://gruntjs.com/) to build it.

## Installing the requirements

In order to use Bower and Grunt we need [node.js](http://nodejs.org/) and its package manager [npm](https://www.npmjs.org/).

On Fedora, Node.js and npm can be installed like this:

    sudo yum install npm

We can now install Bower and Grunt:

    sudo npm install -g bower grunt

## Installing the dependencies

    npm install
    bower install

## Building the website

    grunt

