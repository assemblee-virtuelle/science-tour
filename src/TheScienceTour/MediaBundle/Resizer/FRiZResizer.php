<?php

namespace TheScienceTour\MediaBundle\Resizer;

use Imagine\Image\ImagineInterface;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Gaufrette\File;
use Sonata\MediaBundle\Model\MediaInterface;
use Sonata\MediaBundle\Metadata\MetadataBuilderInterface;

use Sonata\MediaBundle\Resizer\ResizerInterface as ResizerInterface;

class FRiZResizer implements ResizerInterface {
    protected $adapter;
    protected $mode;
    protected $metadata;

    public function __construct(ImagineInterface $adapter, $mode, MetadataBuilderInterface $metadata){
        $this->adapter  = $adapter;
        $this->mode     = $mode;
        $this->metadata = $metadata;
    }

    public function resize(MediaInterface $media, File $in, File $out, $format, array $settings) {
        if (!isset($settings['width'])) {
            throw new \RuntimeException(sprintf('Width parameter is missing in context "%s" for provider "%s"', $media->getContext(), $media->getProviderName()));
        }
        $image = $this->adapter->load($in->getContent());
        $size = $media->getBox();        
        if ($settings['height'] != null) {
        	$crop = $this->getBox($media, $settings);
	    	$image = $image->crop(new Point(($size->getWidth() - $crop->getWidth()) / 2, ($size->getHeight() - $crop->getHeight()) / 2), new Box($crop->getWidth(), $crop->getHeight()));
	    	if ($crop->getWidth() > $settings['width']) {
	    		$image = $image->thumbnail(new Box($settings['width'], $settings['height']), $this->mode);
	    	}
        } else {
        	if ($settings['width'] < $size->getWidth()) {
        		$image = $image->thumbnail(new Box($settings['width'], (int) ($settings['width'] * $size->getHeight() / $size->getWidth())), $this->mode);
        	}
        }
        $content = $image->get($format, array('quality' => $settings['quality']));
        $out->setContent($content, $this->metadata->get($media, $out->getName()));
    }

    public function getBox(MediaInterface $media, array $settings) {
        $size = $media->getBox();
    	if ($size->getWidth() * $settings['height'] < $settings['width'] * $size->getHeight()) {
    		 return new Box($size->getWidth(), (int) ($size->getWidth() * $settings['height'] / $settings['width']));
    	} else {
    		 return new Box((int) ($size->getHeight() * $settings['width'] / $settings['height']), $size->getHeight());
    	}
    }
}