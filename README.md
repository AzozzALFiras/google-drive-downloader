# Google Drive File Downloader

This PHP class allows you to download files from Google Drive, save them locally, and handle file size formatting. It includes features like retries for failed downloads and automatic extraction of the file name from Google Drive headers.

### Developer
- **Azozz ALFiras**

## Table of Contents
1. [Overview](#overview)
2. [Installation](#installation)
3. [Usage](#usage)
4. [License](#license)

## Overview

The `GoogleDriveService` class provides an easy way to download files from Google Drive and save them locally to your server. It handles the following:

- **File Downloading:** Allows you to download a file from Google Drive by providing its URL.
- **Retries:** Supports retrying failed download attempts.
- **Size Formatting:** Converts file sizes into a human-readable format (e.g., MB, GB).
- **Error Handling:** Handles errors such as invalid URLs or missing file IDs.
- **File Saving:** Saves the downloaded file to a local directory.
- **Extracting Download URL:** Extracts the correct download URL from the HTML response content.
- **Automatic Filename Extraction:** Automatically extracts the filename from the response headers or uses a default name if not available.

## Installation

To use the `GoogleDriveService` class in your project, follow these steps:

### 1. Install the Package via Composer

You can install the package `azozzalfiras/3z-gDriverDownload` using Composer. Run the following command in your project directory:

```bash

composer require azozzalfiras/google-drive-service:^1.0

```


2. Include the Class in Your Project
Once installed, simply include the GoogleDriveService.php file in your PHP script:

```php
require_once 'vendor/autoload.php';

use GoogleDriveService\GoogleDriveService;
```


# Usage

Create an Instance of GoogleDriveService
To start using the class, you need to create an instance of GoogleDriveService and use the downloadFile method to download a file.

Download File Example
```php 
require_once 'vendor/autoload.php';

use GoogleDriveService\GoogleDriveService;

$googleDriveService = new GoogleDriveService();
$url = 'your-google-drive-file-url';
$googleDriveService->downloadFile($url);

```




## Authors

- [@AzozzALFiras](https://www.github.com/azozzalfiras)




## License

[MIT](https://choosealicense.com/licenses/mit/)

MIT License

Copyright (c) 2024 Azozz ALFiras

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
