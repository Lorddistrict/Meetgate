# MeetGate - Setup

![](https://img.shields.io/github/stars/Lorddistrict/meetgate.svg) ![](https://img.shields.io/github/forks/Lorddistrict/meetgate.svg) ![](https://img.shields.io/github/tag/Lorddistrict/meetgate.svg) ![](https://img.shields.io/github/issues/Lorddistrict/meetgate.svg) ![](https://img.shields.io/github/issues-pr/Lorddistrict/meetgate.svg)


**Table of Contents**

[TOCM]

[TOC]

#Start the project
##Docker

To start the project you need to have <abbr title="https://docs.docker.com/install/">docker</abbr>, <abbr title="https://docs.docker.com/compose/install/">docker-compose</abbr> and <abbr title="https://getcomposer.org/">composer</abbr> installed.
However you can clone and edit the **docker-compose** file in order running your own containers.
##Git
###Command

`$ git clone https://github.com/Lorddistrict/Meetgate.git`

----
##Makefile
###Commands available

**Start the project & all containers**

`make start`

**Enter the php container**

`make exec`

**Start tests with <abbr title="https://github.com/phpstan/phpstan">phpstan</abbr>**

`make test`

**Start code checking with <abbr title="https://github.com/squizlabs/PHP_CodeSniffer">phpcs</abbr>**

`make testF`
----
##CircleCI

[![CircleCI](https://circleci.com/gh/Lorddistrict/Meetgate.svg?style=svg)](https://circleci.com/gh/Lorddistrict/Meetgate) 

We put CircleCI on the project with 6/7 difficulty.
**App\src\Migrations** are **ignored** because of their length.


----
##Errors

Our Symfony level is around 30, we're still farming gobelins & wolfs apologies.
> I've got an error with a directory named tmp/

Please create one at the root of the project (like **var/**) and use the following command

`sudo chmod -R 777 tmp/`

**WARNING !** Be sure you're on the project folder !

> I've got an error with php.ini

We're using our own **php.ini** config file. If it doesn't match with your config, be free to edit it. We couldn't fetch all php errors you can get :p

> What's docker ?

*Close the door*

----

##How the project works ?

This project has been created to increase the ease for users creating events, those who want to teach others and those who want to learn something.
It allow you to **create** and **manage** events, talks with a **tag** & **rating** system !
This project is a baby but i wish it will become a nice tool for event creators !

It's free to use but not finished at all :D
See ya'

Lorddistrict

----
**Project by**
Etienne Crespi
Arthur Djikpo
