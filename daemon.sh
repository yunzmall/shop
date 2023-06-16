#!/usr/bin/env sh
currentPatch=$(cd $(dirname ${BASH_SOURCE:-$0});pwd)

trap "$1 ${currentPatch}/artisan shop stop" EXIT

$1 ${currentPatch}/artisan shop stop
$1 ${currentPatch}/artisan shop start
