# Compar
add to:

use Compar\CodeceptionCompar\JsonContains;

"class General"

public function seeResponseContainsJsonCompare($json = [])
    {
        Assert::assertThat(
            $this->getModule('REST')->grabResponse(),
            new  JsonContains($json)
        );
    }
