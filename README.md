# Compar


add to "class General"

public function seeResponseContainsJsonCompare($json = [])
    {
        Assert::assertThat(
            $this->getModule('REST')->grabResponse(),
            new  JsonContains($json)
        );
    }
