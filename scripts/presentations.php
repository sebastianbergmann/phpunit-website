#!/usr/bin/env php
<?php
$reader = new XMLReader;
$reader->open(__DIR__ . '/../presentations.xml');

$presentations = array();
$videos        = array();

while ($reader->read()) {
    if ($reader->nodeType !== XMLReader::ELEMENT) {
        continue;
    }

    switch ($reader->name) {
        case 'presentation': {
            $presentations[] = new Presentation(
                $reader->getAttribute('presenter'),
                $reader->getAttribute('title'),
                $reader->getAttribute('link'),
                $reader->getAttribute('image')
            );
        }
        break;

        case 'video': {
            if ($reader->getAttribute('type') == 'vimeo') {
                $class = 'VimeoVideo';
            } else {
                $class = 'YouTubeVideo';
            }

            $videos[] = new $class(
                $reader->getAttribute('presenter'),
                $reader->getAttribute('title'),
                $reader->getAttribute('id')
            );
        }
    }
}

if (count($presentations) % 3 != 0) {
    die('Number of presentations not divisible by 3');
}

if (count($videos) % 3 != 0) {
    die('Number of presentations not divisible by 3');
}

$buffer = file_get_contents(__DIR__ . '/templates/presentations_header.html');

$rows = count($videos) / 3;
for ($row = 1; $row <= $rows; $row++) {

    $left   = array_shift($videos);
    $middle = array_shift($videos);
    $right  = array_shift($videos);

    $buffer .= sprintf(
        '   <div class="row"><div class="col-md-4"><h3>%s</h3></div><div class="col-md-4"><h3>%s</h3></div><div class="col-md-4"><h3>%s</h3></div></div>' . "\n",
        $left->getTitle(),
        $middle->getTitle(),
        $right->getTitle()
    );

    $buffer .= sprintf(
        '   <div class="row"><div class="col-md-4"><h4>by %s</h4></div><div class="col-md-4"><h4>by %s</h4></div><div class="col-md-4"><h4>by %s</h4></div></div>' . "\n",
        $left->getPresenter(),
        $middle->getPresenter(),
        $right->getPresenter()
    );

    $buffer .= sprintf(
        '   <div class="row"><div class="col-md-4">%s</div><div class="col-md-4">%s</div><div class="col-md-4">%s</div></div>' . "\n",
        $left->getIframe(),
        $middle->getIframe(),
        $right->getIframe()
    );
}

$buffer .= file_get_contents(__DIR__ . '/templates/presentations_middle.html');

$rows = count($presentations) / 3;
for ($row = 1; $row <= $rows; $row++) {
    $left   = array_shift($presentations);
    $middle = array_shift($presentations);
    $right  = array_shift($presentations);

    $buffer .= sprintf(
        '   <div class="row"><div class="col-md-4"><h3>%s</h3></div><div class="col-md-4"><h3>%s</h3></div><div class="col-md-4"><h3>%s</h3></div></div>' . "\n",
        $left->getTitle(),
        $middle->getTitle(),
        $right->getTitle()
    );

    $buffer .= sprintf(
        '   <div class="row"><div class="col-md-4"><h4>by %s</h4></div><div class="col-md-4"><h4>by %s</h4></div><div class="col-md-4"><h4>by %s</h4></div></div>' . "\n",
        $left->getPresenter(),
        $middle->getPresenter(),
        $right->getPresenter()
    );

    $buffer .= sprintf(
        '   <div class="row"><div class="col-md-4"><p><a href="%s"><img class="img-responsive img-rounded" alt="%s: %s" src="%s" /></a></p></div><div class="col-md-4"><p><a href="%s"><img class="img-responsive img-rounded" alt="%s: %s" src="%s" /></a></p></div><div class="col-md-4"><p><a href="%s"><img class="img-responsive img-rounded" alt="%s: %s" src="%s" /></a></p></div></div>' . "\n",
        $left->getLink(),
        $left->getPresenter(),
        $left->getTitle(false),
        $left->getImage(),
        $middle->getLink(),
        $middle->getPresenter(),
        $middle->getTitle(false),
        $middle->getImage(),
        $right->getLink(),
        $right->getPresenter(),
        $right->getTitle(false),
        $right->getImage()
    );
}

$buffer .= file_get_contents(__DIR__ . '/templates/presentations_footer.html');

file_put_contents(__DIR__ . '/../public/presentations.html', $buffer);

abstract class Item
{
    private $presenter;
    private $title;

    public function __construct($presenter, $title)
    {
        $this->presenter = $presenter;
        $this->title     = $title;
    }

    public function getPresenter()
    {
        return $this->presenter;
    }

    public function getTitle($breakAfterDoubleColon = true)
    {
        if ($breakAfterDoubleColon) {
            return str_replace(':', ':<br/>', $this->title);
        } else {
            return $this->title;
        }
    }
}

class Presentation extends Item
{
    private $link;
    private $image;

    public function __construct($presenter, $title, $link, $image)
    {
        parent::__construct($presenter, $title);

        $this->link      = $link;
        $this->image     = $image;
    }

    public function getLink()
    {
        return $this->link;
    }

    public function getImage()
    {
        return $this->image;
    }
}

abstract class Video extends Item
{
    private $id;

    public function __construct($presenter, $title, $id)
    {
        parent::__construct($presenter, $title);

        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    abstract public function getIframe();
}

class VimeoVideo extends Video
{
    public function getIframe()
    {
        return sprintf(
            '<div class="flex-video vimeo"><iframe src="https://player.vimeo.com/video/%s?title=0&amp;byline=0&amp;portrait=0"></iframe></div>',
            $this->getId()
        );
    }
}

class YouTubeVideo extends Video
{
    public function getIframe()
    {
        return sprintf(
            '<div class="flex-video widescreen"><iframe src="https://www.youtube-nocookie.com/embed/%s?rel=0&amp;wmode=opaque&amp;showinfo=0&amp;rel=0&amp;theme=light"></iframe></div>',
            $this->getId()
        );
    }
}

