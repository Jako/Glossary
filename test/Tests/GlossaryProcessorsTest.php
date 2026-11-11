<?php
/**
 * Glossary Processors Tests
 *
 * @package glossary
 * @subpackage test
 */

class GlossaryProcessorsTest extends GlossaryTestCase
{
    /**
     * @dataProvider validTermProvider
     */
    public function testCreateValidTerm(string $suffix)
    {
        $term = self::TEST_PREFIX . uniqid() . $suffix;

        $response = $this->runProcessor('create', ['term' => $term, 'explanation' => 'Explanation']);

        /** @var Term $object */
        $object = $this->modx->getObject('Term', ['term' => $term]);
        if ($object) {
            $this->trackObject($object);
        }
        $this->assertFalse($response->isError(), $response->getMessage() . implode(", ", $this->getFieldErrors($response)));
        $this->assertNotNull($object);
        $this->assertSame('Explanation', $object->get('explanation'));
        $this->assertSame((int)$this->modx->user->get('id'), (int)$object->get('createdby'));
    }

    public static function validTermProvider(): array
    {
        return [
            'letters' => [' Foo'],
            'unicode letters' => [' Ärger über Привет'],
            'numbers' => [' 2024'],
            'allowed characters' => [' a:b.c;d,e-f_g'],
        ];
    }

    /**
     * @dataProvider invalidTermProvider
     */
    public function testCreateInvalidTerm(string $suffix)
    {
        $term = self::TEST_PREFIX . uniqid() . $suffix;

        $response = $this->runProcessor('create', ['term' => $term, 'explanation' => 'Explanation']);

        $this->assertTrue($response->isError());
        $this->assertArrayHasKey('term', $this->getFieldErrors($response));
        $this->assertSame(0, $this->modx->getCount('Term', ['term' => $term]));
    }

    public static function invalidTermProvider(): array
    {
        return [
            'html' => ['<b>'],
            'quotes' => ['"Foo"'],
            'plus' => ['C++'],
            'ampersand' => [' & Bar'],
        ];
    }

    public function testCreateRequiredFields()
    {
        $response = $this->runProcessor('create', ['term' => '', 'explanation' => '']);

        $this->assertTrue($response->isError());
        $this->assertEqualsCanonicalizing(['term', 'explanation'], array_keys($this->getFieldErrors($response)));
    }

    public function testCreateDuplicateTerm()
    {
        $term = $this->createTerm(self::TEST_PREFIX . uniqid());

        $response = $this->runProcessor('create', ['term' => $term->get('term'), 'explanation' => 'Explanation']);

        $this->assertTrue($response->isError());
        $this->assertArrayHasKey('term', $this->getFieldErrors($response));
        $this->assertSame(1, $this->modx->getCount('Term', ['term' => $term->get('term')]));
    }

    public function testUpdateTerm()
    {
        $term = $this->createTerm(self::TEST_PREFIX . uniqid());

        $response = $this->runProcessor('update', [
            'id' => $term->get('id'),
            'term' => $term->get('term') . ' Updated',
            'explanation' => 'Updated',
        ]);

        $this->assertFalse($response->isError(), $response->getMessage() . implode(", ", $this->getFieldErrors($response)));
        $updated = $this->modx->getObject('Term', $term->get('id'));
        $this->assertSame($term->get('term') . ' Updated', $updated->get('term'));
        $this->assertSame('Updated', $updated->get('explanation'));
    }

    public function testUpdateInvalidTerm()
    {
        $term = $this->createTerm(self::TEST_PREFIX . uniqid());

        $response = $this->runProcessor('update', [
            'id' => $term->get('id'),
            'term' => '<script>',
            'explanation' => 'Explanation',
        ]);

        $this->assertTrue($response->isError());
        $this->assertArrayHasKey('term', $this->getFieldErrors($response));
        $this->assertSame($term->get('term'), $this->modx->getObject('Term', $term->get('id'))->get('term'));
    }

    public function testUpdateFromGrid()
    {
        $term = $this->createTerm(self::TEST_PREFIX . uniqid());

        $response = $this->runProcessor('updatefromgrid', [
            'data' => json_encode([
                'id' => $term->get('id'),
                'term' => $term->get('term'),
                'explanation' => 'From grid',
            ]),
        ]);

        $this->assertFalse($response->isError(), $response->getMessage() . implode(", ", $this->getFieldErrors($response)));
        $this->assertSame('From grid', $this->modx->getObject('Term', $term->get('id'))->get('explanation'));
    }

    public function testGetList()
    {
        $prefix = self::TEST_PREFIX . uniqid();
        $this->createTerm($prefix . ' Beta');
        $this->createTerm($prefix . ' Alpha');

        $response = $this->runProcessor('getlist', ['query' => $prefix, 'sort' => 'term', 'dir' => 'ASC']);

        $result = json_decode($response->getResponse(), true);
        $this->assertEquals(2, $result['total']);
        $this->assertSame([$prefix . ' Alpha', $prefix . ' Beta'], array_column($result['results'], 'term'));
    }

    public function testRemoveTerm()
    {
        $term = $this->createTerm(self::TEST_PREFIX . uniqid());

        $response = $this->runProcessor('remove', ['id' => $term->get('id')]);

        $this->assertFalse($response->isError());
        $this->assertNull($this->modx->getObject('Term', $term->get('id')));
    }

    /**
     * Run a Glossary term processor.
     *
     * @param string $action
     * @param array $properties
     * @return modProcessorResponse
     */
    private function runProcessor(string $action, array $properties)
    {
        $response = $this->modx->runProcessor('mgr/term/' . $action, $properties, [
            'processors_path' => $this->glossary->getOption('processorsPath'),
        ]);
        $this->assertNotFalse($response, 'The processor mgr/term/' . $action . ' could not be run.');
        return $response;
    }

    /**
     * Get the field errors of a processor response, keyed by the field name.
     *
     * @param modProcessorResponse $response
     * @return array
     */
    private function getFieldErrors($response): array
    {
        $errors = [];
        foreach ($response->getFieldErrors() as $error) {
            $errors[$error->field] = $error->message;
        }
        return $errors;
    }
}
