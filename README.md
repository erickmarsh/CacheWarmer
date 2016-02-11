# Cache Warmer

**You do not have to clone this repo to use the cache warmer. Just download the warmer.phar file**
**If using the Google Analytics option, you also need the cacerts.pem file**

## Instructions

Run via command line

```
> php warmer.phar [warm:file|warm:ga|warm:sitemap] file_name [options]
```

### Warm By File

Warm by file takes in a single parameter which is a path to a text file which has a list of urls to visit, one url per line

```
> php warmer.phar warm:file <file_path> [--threads --wait]
```

| Param | Default |Description |
| :--- | --- | :--- |
| file_path | none | path to local file |
| --threads | 8 | Number of threads to run while warming cache |
| --wait | 5 | Number of seconds to wait between each batch of treads to kick off |

### Warm By Sitemap

Takes a url of a sitemap and extracts all of the urls from the sitemap. Writes list of urls to var/urls.txt.
Warms all of the parsed urls

```
> php warmer.phar warm:sitemap <sitemap_url> [--threads --wait]
```

| Param | Default |Description |
| :--- | --- | :--- |
| sitemap_url | none | url of sitempa |
| --threads | 8 | Number of threads to run while warming cache |
| --wait | 5 | Number of seconds to wait between each batch of treads to kick off |
| --intermediary_file | /var | Where a log of all of the urls that will be run are stored |

### Warm by Google Analytics

Extracts the specified number of urls from GA sorted by the their visitaion frequency over the timeframe specified. Warms the cache based on that url list. Also writes the url list to var/url.txt

```
> php warmer.phar warm:ga <file_path> <ga_account> <domain> [--count --start_date --end_date --threads --wait]
```

| Param | Default |Description |
| :--- | --- | :--- |
| file_path | none | path to credentials file (see conf/ga-credentials.json) |
| ga_account | none | Google Analytics account ID (ga:123456)|
| domain | none | The domain of the site you want to warm (ex: www.google.com) |
| --count | 1000 | Max number of urls to pull back from GA |
| --start_date| 30daysAgo | Starting date for time range to find the most popular pages |
| --end_date | yesterday | End date for the time range to find the most popular pages |
| --threads | 8 | Number of threads to run while warming cache |
| --wait | 5 | Number of seconds to wait between each batch of treads to kick off |
| --intermediary_file | /var | Where a log of all of the urls that will be run are stored |

## Building the PHAR file

If you do have to clone the repo to make changes, then you have to rebuild the PHAR file and commit it, otherwise others won't see your changes

```
> php build.php
```

## Generating Google Analytics Credentials file

In order to run the the GA, there is a credentials file that needs to be generated from the Google Developers Console https://developers.google.com/console/help/new/

> **Service accounts**
>
> If your project employs server-to-server interactions such as those between a web application and Google Cloud Storage, then you need a private key and other service-account credentials. To generate these credentials, or to view the email address and public keys that you've already generated, do > the following:
>
> 1. Open the Credentials page.
> 2. To set up a new service account, do the following:
>    a. Click Add credentials > Service account.
>    b. Choose whether to download the service account's public/private key as a standard P12 file, or as a JSON file that can be loaded by a Google API client library.
> 
> Your new public/private key pair is generated and downloaded to your machine; it serves as the only copy of this key. You are responsible for storing it securely.
> Your project needs the private key when requesting an OAuth 2.0 access token in server-to-server interactions. Google does not keep a copy of this private key, and this screen is the only place to obtain this particular private key. When you click Download private key, the PKCS #12-formatted >private key is downloaded to your local machine. As the screen indicates, you must securely store this key yourself.


## Getting the "ga" number (the second parameter)

The "GA" parameter is linked to a Google Analytics loggin. The easist way to find this number seems quite hacky, but it works.

1. Login to Google Analytics with the account that has access to the client's Analytics
2. The last param in the URL is the "View ID" that we need. 

    > EX: The number we need is 15407048
    > 
    > Google Analytics URL after logging in:
    >
    > https://analytics.google.com/analytics/web/?authuser=2#home/a7647357w14607173p15407048/ 
    >
    > Notice that the number we need is listed after the "p" at the very end of the URL

3. Copy that number and put it into the command line