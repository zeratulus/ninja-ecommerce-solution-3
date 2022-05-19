# monsters-shop-cms
CMS&CRM system for eCommerce solutions by Monsters Studio

##Code Standard PHP:
###0. PSR-0
###1. Variables naming:
$some_variable = '';

$some_array['some_array_key'] = '';

###2. Function naming:
function someFunctionName($arg1, $arg2, ... ,$argN) {}

function withTypes(string $name): string {

//do Something

return $some_thing;

}


function withClassTypes(\Request $request): string {

//do Something

return $some_thing;

}

###3. Class naming:
class ControllerRouteFilename extends Controller {}

class ModelRouteFilename extends Model {}

###4. Class methods naming:
class ControllerRouteFilename extends Controller {

private $__your_private_property;

protected $_your_protected_property;

public $your_public_property;

private function setSomethingBeautiful() {

//do Something

return $some_thing;

}

public function index() {

$this->setSomethingBeautiful();

};

public function edit_your_api() {};

public function remove_your_api() {};

}

###5. Code nesting
For display the level of code nesting use Tab = 4 spaces

###QodanaPHP:
docker run --rm -it -p 8080:8080 \
-v <source-directory>/:/data/project/ \
-v <output-directory>/:/data/results/ \
jetbrains/qodana-php --show-report

docker run --rm -it -p 8080:8080 -v C:/Projects/monsters-store-3/public/:/data/project/ -v C:/Projects/monsters-store-3/qodana/:/data/results/ jetbrains/qodana-php --show-report