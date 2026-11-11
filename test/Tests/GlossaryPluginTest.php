<?php
/**
 * Glossary Plugin Tests
 *
 * @package glossary
 * @subpackage test
 */

use TreehillStudio\Glossary\Plugins\Events\OnLoadWebDocument;

class GlossaryPluginTest extends GlossaryTestCase
{
    /**
     * @var string The term created by the test
     */
    private $term;

    /**
     * @var modResource|null The resource before the test
     */
    private $originalResource;

    public function setUp(): void
    {
        parent::setUp();
        $this->originalResource = $this->modx->resource;

        $this->term = 'Zz' . strtolower(self::TEST_PREFIX) . uniqid();
        $this->createTerm($this->term);
        $this->glossary->options['tpl'] = $this->createChunk('<span>[[+term]]</span>');
        $this->glossary->options['resid'] = $this->getExistingResourceId();
        $this->glossary->options['enabledContexts'] = '';
        $this->glossary->options['enabledTemplates'] = '';
    }

    public function tearDown(): void
    {
        $this->modx->resource = $this->originalResource;
        parent::tearDown();
    }

    public function testHighlightHtmlResource()
    {
        $content = $this->runPlugin($this->newResource('modDocument', 'text/html'));

        $this->assertSame('<p><span>' . $this->term . '</span></p>', $content);
    }

    public function testSkipTargetResource()
    {
        $resource = $this->newResource('modDocument', 'text/html');
        $resource->set('id', $this->glossary->options['resid']);

        $this->assertSame($this->content(), $this->runPlugin($resource));
    }

    /**
     * @dataProvider skippedMimeTypeProvider
     */
    public function testSkipNonHtmlContentTypes(string $mimeType)
    {
        $this->assertSame($this->content(), $this->runPlugin($this->newResource('modDocument', $mimeType)));
    }

    public static function skippedMimeTypeProvider(): array
    {
        return [
            'json' => ['application/json'],
            'xml' => ['text/xml'],
            'css' => ['text/css'],
            'plain text' => ['text/plain'],
        ];
    }

    /**
     * @dataProvider skippedClassProvider
     */
    public function testSkipResourceClasses(string $class)
    {
        $content = 'assets/files/' . $this->term . '.pdf';

        $this->assertSame($content, $this->runPlugin($this->newResource($class, 'text/html', $content)));
    }

    public static function skippedClassProvider(): array
    {
        return [
            'static resource' => ['modStaticResource'],
            'weblink' => ['modWebLink'],
            'symlink' => ['modSymLink'],
        ];
    }

    public function testSkipMissingTargetResource()
    {
        $this->glossary->options['resid'] = 0;

        $this->assertSame($this->content(), $this->runPlugin($this->newResource('modDocument', 'text/html')));
    }

    /**
     * @dataProvider enabledListProvider
     */
    public function testEnabledContextsAndTemplates(string $option, string $value, bool $highlighted)
    {
        $resource = $this->newResource('modDocument', 'text/html');
        $resource->set('template', 2);
        $this->glossary->options[$option] = $value;

        $content = $this->runPlugin($resource);

        $this->assertSame($highlighted, $content !== $this->content());
    }

    public static function enabledListProvider(): array
    {
        return [
            'context enabled' => ['enabledContexts', 'mgr, web', true],
            'context disabled' => ['enabledContexts', 'mgr,other', false],
            'template enabled' => ['enabledTemplates', '1, 2', true],
            'template disabled' => ['enabledTemplates', '1,3', false],
        ];
    }

    /**
     * Run the plugin event with a resource and return the resource content.
     *
     * @param modResource $resource
     * @return string
     */
    private function runPlugin($resource): string
    {
        $this->modx->resource = $resource;
        $scriptProperties = [];
        $plugin = new OnLoadWebDocument($this->modx, $scriptProperties);
        $plugin->run();
        return $resource->get('content');
    }

    /**
     * Create an unsaved resource with the test content.
     *
     * @param string $class
     * @param string $mimeType
     * @param string|null $content
     * @return modResource
     */
    private function newResource(string $class, string $mimeType, ?string $content = null)
    {
        /** @var modContentType $contentType */
        $contentType = $this->modx->getObject('modContentType', ['mime_type' => $mimeType]);
        if (!$contentType) {
            $this->markTestSkipped('No content type with the mime type ' . $mimeType . ' exists.');
        }
        /** @var modResource $resource */
        $resource = $this->modx->newObject($class);
        $resource->fromArray([
            'id' => 0,
            'context_key' => 'web',
            'template' => 1,
            'content_type' => $contentType->get('id'),
            'content' => $content ?? $this->content(),
        ], '', true);
        return $resource;
    }

    /**
     * @return string
     */
    private function content(): string
    {
        return '<p>' . $this->term . '</p>';
    }

    /**
     * @return int
     */
    private function getExistingResourceId(): int
    {
        $c = $this->modx->newQuery('modResource');
        $c->select('id');
        $c->sortby('id', 'ASC');
        $c->limit(1);
        $id = (int)$this->modx->getValue($c->prepare());
        if (!$id) {
            $this->markTestSkipped('No resource exists.');
        }
        return $id;
    }
}
