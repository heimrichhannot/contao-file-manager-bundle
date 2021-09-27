# Contao File Manager Bundle

This bundle offers a frontend file manager for the Contao CMS.

## Features

- display a simple and clean file manager in the frontend
- built with security in mind:
  - takes care of the "public" state of folders
  - always checks if the website user is allowed to see the current folder
  - select allowed folders in a global file manager configuration or based on a member login in member groups separately

## Impressions

![The file manager in the frontend](docs/img/file-manager.png "The file manager in the frontend")

The file manager in the frontend

## Installation

1. Install via composer: `composer require heimrichhannot/contao-file-manager-bundle`.
2. Update your database using migration command or install tool as usual.

## Usage

1. Create a file manager config in the contao backend.
2. Create member groups if the file manager is non-public. Here you can extend the permissions of the file manager config.
3. Create a frontend module and assign the file manager config. Then place the module in an article you like.

## TODO

- batch processing
- actions: copy, move, rename, upload
