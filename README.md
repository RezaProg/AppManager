# AppManager
A simple php script for management your apps
You can manage and license your apps by this script
## Example
Setting application properties (status and version):
<pre>http://site.com/AppManager.php?query=setAppProperties&status=true&version=1.2.0.0</pre>
Getting application properties (status and version):
<pre>http://site.com/AppManager.php?query=getAppProperties</pre>
Enable a license:
<pre>http://site.com/AppManager.php?query=enableLicense&license=REZAGLZ&owner=reza</pre>
Disable a license:
<pre>http://site.com/AppManager.php?query=disableLicense&license=REZAGLZ</pre>
Validation a license:
<pre>http://site.com/AppManager.php?query=validationLicense&license=REZAGLZ</pre>
Get licenses list:
<pre>http://site.com/AppManager.php?query=getLicensesList</pre>
Generate license:
<pre>http://site.com/AppManager.php?query=generateLicense</pre>