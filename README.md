# API - Find My Friends
[![Build Status](https://travis-ci.org/FindMyFriends/api.svg?branch=master)](https://travis-ci.org/FindMyFriends/api)
[![codecov](https://codecov.io/gh/FindMyFriends/api/branch/master/graph/badge.svg)](https://codecov.io/gh/FindMyFriends/api)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)

## Motivation
The app serves for finding people based on physical similarity. It means you can search person you have met but you do not have opportunity to ask for a name.

## Parts
This is a simple REST API in JSON serving as data source for React application. It is still in progress and closed for public usage. In fact it is still experimental application to prove there is also other way how to write robust application. A lot of parts are therefore not perfect and waits for real usage with real traffic.

## Next steps
Please see issues for more information.

## Installation
Import certificate (`docker/nginx/ssl/rootCA-Development.pem`) to your browser to support HTTPS.

Run docker environment, then exec into php image and run:
`make init`
then exec into database image and run:
`test_import`
go gack to php image and run tests via:
`make tests`
