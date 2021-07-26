# Create CloudFront Custom Policy signed URLs in Laravel 6.0+

Easy to use Laravel 6+ | 7+ | 8+ wrapper which allows to sign URLs to access Private Content through CloudFront CDN

This package can create custom policy signed URLs for CloudFront which expires after a given time. The default time is set to 86400 seconds or 24 hours. This package does not depends on AWS SDK package, but implements the example from (https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/CreateURL_PHP.html#sample-custom-policy)

The benefit of this approach is that you can use the same signature for multiple files and hence the processing is much much faster when you are loading multiple signed images/videos or other files on a single page.

This is how you can create signed URL that's valid for 1 day or 24 hour:

## Usage

### Signing URLs

```php
// Add Namespace in Controller
use Aws\CustomPolicyUrl\CustomPolicyUrl as AWSURL;

/*
 * Instantiate the class and pass the array with values matching 
 * the path of the media files in the AWS S3 Bubket
 */

		$key_id = config('aws-custom-policy-url.KEY-ID');
		
    	$aws_private_key = config('aws-custom-policy-url.AWS-PRIVATE-KEY');
    	
    	$path_arr = array('sample_5mb.mp4','sample_3mb.mp4'); // get the sample files either from your own DB or changing the storage config to s3 bucket and fetching the list of files. Ref.: (https://laravel.com/docs/8.x/filesystem#retrieving-files)

     	$aws_obj = New AWSURL($key_id, $aws_private_key);
    	$get_signed_urls = $aws_obj->getSignedUrls($path_arr);

```

The output is compliant with [CloudFront specifications](https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/private-content-creating-signed-url-custom-policy.html)

## Installation

The package can be installed via Composer:

```
composer require epiphanyinfotech/laravel-cloudfront-custom-policy-url-signer
```

## Configuration

The configuration file can optionally be published via:

```
php artisan vendor:publish --provider="Aws\CustomPolicyUrl\CustomPolicyUrlServiceProvider"
```

This is the content of the file:

```php
return [
    /*
     * The cloudfront public keys id. Identifies the CloudFront key pair associated to the trusted signer which validates signed URLs.
     * To create a key, refer: https://console.aws.amazon.com/cloudfront/v3/home?region=us-east-2#/publickey
     * Make sure to replace region=us-east-2 with your region
     */
    'KEY-ID' => env("AWS_KEY_ID", NULL),

    /*
     * The private key used to sign all URLs. Make sure it's secured and not accessible to public. Enter absolute path in the .env file
     */
    "AWS-PRIVATE-KEY" => env("AWS_PRIVATE_KEY_FILEPATH", NULL),

    /*
     * The IP address of the client making the GET request. For further info, visit: (https://docs.aws.amazon.com/AmazonCloudFront/latest/DeveloperGuide/private-content-creating-signed-url-custom-policy.html) and search for - "IpAddress (Optional)"
     */
    "CLIENT_IP" => env("AWS_CLIENT_IP", NULL),,

    /*
     * Your CloudFront domain URL. To check your cloudfront domain, visit: (https://console.aws.amazon.com/cloudfront/v3/home?region=us-east-2#/distributions)
	 * Make sure to replace region=us-east-2 with your region.
	 * The "Domain Name" is your URL. If you have configured cloudfront to use the custom domain, then enter that.
     */
    "AWS_URL" => env("AWS_URL", NULL),

];
```


## Security

If you discover any security related issues, please email info@epiphanyinfotech.com or use the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
