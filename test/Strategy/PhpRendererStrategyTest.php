<?php

/**
 * @see       https://github.com/laminas/laminas-view for the canonical source repository
 * @copyright https://github.com/laminas/laminas-view/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-view/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\View\Strategy;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\EventManager\EventManager;
use Zend\Http\Response as HttpResponse;
use Zend\View\Renderer\PhpRenderer;
use Zend\View\Strategy\PhpRendererStrategy;
use Zend\View\ViewEvent;

class PhpRendererStrategyTest extends TestCase
{
    public function setUp()
    {
        $this->renderer = new PhpRenderer;
        $this->strategy = new PhpRendererStrategy($this->renderer);
        $this->event    = new ViewEvent();
        $this->response = new HttpResponse();
    }

    public function testSelectRendererAlwaysSelectsPhpRenderer()
    {
        $result = $this->strategy->selectRenderer($this->event);
        $this->assertSame($this->renderer, $result);
    }

    protected function assertResponseNotInjected()
    {
        $content = $this->response->getContent();
        $headers = $this->response->getHeaders();
        $this->assertEmpty($content);
        $this->assertFalse($headers->has('content-type'));
    }

    public function testNonMatchingRendererDoesNotInjectResponse()
    {
        $this->event->setResponse($this->response);

        // test empty renderer
        $this->strategy->injectResponse($this->event);
        $this->assertResponseNotInjected();

        // test non-matching renderer
        $renderer = new PhpRenderer();
        $this->event->setRenderer($renderer);
        $this->strategy->injectResponse($this->event);
        $this->assertResponseNotInjected();
    }

    public function testResponseContentSetToContentPlaceholderWhenResultAndArticlePlaceholderAreEmpty()
    {
        $this->renderer->placeholder('content')->set('Content');
        $event = new ViewEvent();
        $event->setResponse($this->response)
              ->setRenderer($this->renderer);

        $this->strategy->injectResponse($event);
        $content = $this->response->getContent();
        $this->assertEquals('Content', $content);
    }

    public function testResponseContentSetToArticlePlaceholderWhenResultIsEmptyAndBothArticleAndContentPlaceholdersSet()
    {
        $this->renderer->placeholder('article')->set('Article Content');
        $this->renderer->placeholder('content')->set('Content');
        $event = new ViewEvent();
        $event->setResponse($this->response)
              ->setRenderer($this->renderer);

        $this->strategy->injectResponse($event);
        $content = $this->response->getContent();
        $this->assertEquals('Article Content', $content);
    }

    public function testResponseContentSetToResultIfNotEmpty()
    {
        $this->renderer->placeholder('article')->set('Article Content');
        $this->renderer->placeholder('content')->set('Content');
        $event = new ViewEvent();
        $event->setResponse($this->response)
              ->setRenderer($this->renderer)
              ->setResult('Result Content');

        $this->strategy->injectResponse($event);
        $content = $this->response->getContent();
        $this->assertEquals('Result Content', $content);
    }

    public function testContentPlaceholdersIncludeContentAndArticleByDefault()
    {
        $this->assertEquals(['article', 'content'], $this->strategy->getContentPlaceholders());
    }

    public function testContentPlaceholdersListIsMutable()
    {
        $this->strategy->setContentPlaceholders(['foo', 'bar']);
        $this->assertEquals(['foo', 'bar'], $this->strategy->getContentPlaceholders());
    }

    public function testAttachesListenersAtExpectedPriorities()
    {
        $events = new EventManager();
        $events->attachAggregate($this->strategy);

        foreach (['renderer' => 'selectRenderer', 'response' => 'injectResponse'] as $event => $method) {
            $listeners        = $events->getListeners($event);
            $expectedCallback = [$this->strategy, $method];
            $expectedPriority = 1;
            $found            = false;
            foreach ($listeners as $listener) {
                $callback = $listener->getCallback();
                if ($callback === $expectedCallback) {
                    if ($listener->getMetadatum('priority') == $expectedPriority) {
                        $found = true;
                        break;
                    }
                }
            }
            $this->assertTrue($found, 'Listener not found');
        }
    }

    public function testCanAttachListenersAtSpecifiedPriority()
    {
        $events = new EventManager();
        $events->attachAggregate($this->strategy, 100);

        foreach (['renderer' => 'selectRenderer', 'response' => 'injectResponse'] as $event => $method) {
            $listeners        = $events->getListeners($event);
            $expectedCallback = [$this->strategy, $method];
            $expectedPriority = 100;
            $found            = false;
            foreach ($listeners as $listener) {
                $callback = $listener->getCallback();
                if ($callback === $expectedCallback) {
                    if ($listener->getMetadatum('priority') == $expectedPriority) {
                        $found = true;
                        break;
                    }
                }
            }
            $this->assertTrue($found, 'Listener not found');
        }
    }

    public function testDetachesListeners()
    {
        $events = new EventManager();
        $events->attachAggregate($this->strategy);
        $listeners = $events->getListeners('renderer');
        $this->assertEquals(1, count($listeners));
        $listeners = $events->getListeners('response');
        $this->assertEquals(1, count($listeners));
        $events->detachAggregate($this->strategy);
        $listeners = $events->getListeners('renderer');
        $this->assertEquals(0, count($listeners));
        $listeners = $events->getListeners('response');
        $this->assertEquals(0, count($listeners));
    }
}
