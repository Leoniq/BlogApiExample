<?php

namespace App\Infrastructure\Service\ParamConverter;

use App\Domain\Entity\Post;
use App\Domain\Exception\Post\PostNotFoundException;
use App\Infrastructure\Repository\PostRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\HttpFoundation\Request;

class PostParamConverter implements ParamConverterInterface
{
    private PostRepository $repository;

    /**
     * PostParamConverter constructor.
     * @param PostRepository $repository
     */
    public function __construct(PostRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param Request $request
     * @param ParamConverter $configuration
     *
     * @return bool
     *
     * @throws PostNotFoundException
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $postId = $request->attributes->get('id');

        if (null !== $postId) {
            $post = $this->repository->find($postId);

            if (null === $post) {
                throw new PostNotFoundException();
            }
        }

        $request->attributes->set($configuration->getName(), $post);

        return true;
    }

    /**
     * @param ParamConverter $configuration
     * @return bool
     */
    public function supports(ParamConverter $configuration)
    {
        return Post::class === $configuration->getClass();
    }
}
