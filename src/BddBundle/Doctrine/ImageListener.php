<?php

namespace BddBundle\Doctrine;

use BddBundle\Entity\Image;
use BddBundle\Service\ImageManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class ImageListener
 * @package BddBundle\Doctrine
 */
class ImageListener
{
    /**
     * @var ImageManager
     */
    private $imageManager;

    /**
     * ImageUploadListener constructor.
     * @param ImageManager $imageManager
     */
    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    /**
     * Before persist:
     *  - upload file
     *
     * @param LifecycleEventArgs $args
     * @throws \Exception
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $image = $args->getEntity();
        if (!$image instanceof Image) {
            return;
        }

        $file = $image->getFile();

        // only upload new files
        if ($file instanceof UploadedFile) {
            $fileName = $this->imageManager->upload($file);
            $image->setFilename($fileName);
        }
    }

    /**
     * Before remove :
     *  - remove image file on disk
     *
     * @param LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $image = $args->getEntity();
        if (!$image instanceof Image) {
            return;
        }

        $this->imageManager->remove($image->getFilename());
    }

    /**
     * After remove :
     *  - update main images
     *  - remove cache
     *
     * @param LifecycleEventArgs $args
     */
    public function postRemove(LifecycleEventArgs $args)
    {
        $image = $args->getEntity();
        if (!$image instanceof Image) {
            return;
        }

        $this->imageManager->setMainImages();

        $this->imageManager->removeCache($image);
    }

    /**
     * After update:
     *  - update main images
     *
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        if (!$args->getEntity() instanceof Image) {
            return;
        }

        $this->imageManager->setMainImages();
    }
}
