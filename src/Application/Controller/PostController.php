<?php

namespace App\Application\Controller;

use App\Application\Form\Command\Post\PostDeleteCommand;
use App\Application\Form\Command\Post\PostUpdateCommand;
use App\Application\Form\Type\Post\PostUpdateType;
use App\Domain\Entity\Post;
use App\Domain\Exception\Form\FormValidErrorException;
use App\Application\Form\Command\Post\PostCreateCommand;
use App\Application\Form\Type\Post\PostCreateType;
use App\Infrastructure\Repository\PostRepository;
use App\Infrastructure\Representation\PostRepresentation;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Hateoas\Configuration\Route;
use Hateoas\Representation\CollectionRepresentation;
use Hateoas\Representation\Factory\PagerfantaFactory;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use OpenApi\Annotations as OA;
use App\Infrastructure\Command\Command\CommandBus;
use Pagerfanta\Adapter\FixedAdapter;
use Pagerfanta\Pagerfanta;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PostController extends AbstractController
{
    private CommandBus $commandBus;
    private SerializerInterface $serializer;
    private PostRepository $postRepository;

    /**
     * PostController constructor.
     *
     * @param PostRepository $postRepository
     * @param CommandBus $commandBus
     * @param SerializerInterface $serializer
     */
    public function __construct(
        PostRepository $postRepository,
        CommandBus $commandBus,
        SerializerInterface $serializer
    ) {
        $this->postRepository = $postRepository;
        $this->commandBus = $commandBus;
        $this->serializer = $serializer;
    }

    /**
     * @OA\Post(
     *     tags={"Post"},
     *     summary="Create a post",
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="multipart/form-data",
     *              @OA\Schema(
     *                  required={"title", "description"},
     *                  @OA\Property(
     *                      property="title",
     *                      description="Post title",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      description="Post description",
     *                      type="string"
     *                  ),
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Post created"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Access denied"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws FormValidErrorException
     */
    public function createPostAction(Request $request): JsonResponse
    {
        $command = new PostCreateCommand();
        $command->title = $request->request->get('title');
        $command->description = $request->request->get('description');

        $form = $this->createForm(PostCreateType::class, $command, ['data_class' => PostCreateCommand::class]);
        $form->submit($request->request->all(), false);

        if (!$form->isValid()) {
            throw new FormValidErrorException();
        }

        $this->commandBus->handle($command);

        $post = $this->postRepository->findLastInsert();

        return new JsonResponse(
            $this->serializer->serialize(
                new PostRepresentation($post),
                'json',
                SerializationContext::create()->setGroups(['post_create'])->setSerializeNull(true)
            ),
            JsonResponse::HTTP_CREATED,
            [],
            true
        );
    }

    /**
     * @OA\Get(
     *     tags={"Post"},
     *     summary="List of posts",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Current page to returned. Default is: 1.",
     *         @OA\Schema(type="integer"),
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Maximum number of items requested. Default is: 10.",
     *         @OA\Schema(type="integer"),
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="sortBy",
     *         in="query",
     *         description="Post title",
     *         @OA\Schema(enum={"id", "createdAt", "updatedAt"}),
     *         required=false
     *     ),
     *     @OA\Parameter(
     *         name="orderBy",
     *         in="query",
     *         description="Post title",
     *         @OA\Schema(enum={"ASC", "DESC"}),
     *         required=false
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="List of posts"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Access denied"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function listPostAction(Request $request): JsonResponse
    {
        $page = $request->query->get('page', 1);
        $limit = $request->query->get('limit', 10);
        $filters = [
            'sortBy' => $request->query->get('sortBy'),
            'orderBy' => $request->query->get('orderBy'),
        ];

        $totalPosts = $this->postRepository->findTotal();
        $postRepresentations = [];

        foreach ($this->postRepository->findAllPosts($page, $limit, $filters) as $post) {
            $postRepresentations[] = new PostRepresentation($post);
        }

        $pager = new Pagerfanta(new FixedAdapter($totalPosts, $postRepresentations));
        $pager->setMaxPerPage($limit);
        $pager->setCurrentPage($page);

        $paginatedCollection = (new PagerfantaFactory())->createRepresentation(
            $pager,
            new Route($request->get('_route'), array_merge(
                    $request->get('_route_params'),
                    $request->query->all())
            ),
            new CollectionRepresentation($pager->getCurrentPageResults())
        );

        return new JsonResponse(
            $this->serializer->serialize(
                $paginatedCollection,
                'json',
                SerializationContext::create()->setGroups(['Default', 'post_list'])->setSerializeNull(true)
            ),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @OA\Get(
     *     tags={"Post"},
     *     summary="Post reading",
     *     @OA\Response(
     *         response="200",
     *         description="List of posts"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Access denied"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     *
     * @ParamConverter("post", converter="post")
     *
     * @param Post $post
     *
     * @return JsonResponse
     */
    public function readPostAction(Post $post): JsonResponse
    {
        return new JsonResponse(
            $this->serializer->serialize(
                new PostRepresentation($post),
                'json',
                SerializationContext::create()->setGroups(['post_read'])->setSerializeNull(true)
            ),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @OA\Patch (
     *     tags={"Post"},
     *     summary="Update post",
     *     @OA\RequestBody(
     *          @OA\MediaType(
     *              mediaType="application/x-www-form-urlencoded",
     *              @OA\Schema(
     *                  @OA\Property(
     *                      property="title",
     *                      description="Updated title of the post",
     *                      type="string"
     *                  ),
     *                  @OA\Property(
     *                      property="description",
     *                      description="Updated description of the post",
     *                      type="string"
     *                  ),
     *              )
     *          )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Post updated"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Access denied"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     *
     * @ParamConverter("post", converter="post")
     *
     * @param Request $request
     * @param Post $post
     *
     * @return JsonResponse
     *
     * @throws FormValidErrorException
     */
    public function updatePostAction(Request $request, Post $post): JsonResponse
    {
        $command = new PostUpdateCommand();
        $command->id = $post->getId();
        $command->title = $request->request->get('title');
        $command->description = $request->request->get('description');

        $form = $this->createForm(PostUpdateType::class, $command, ['data_class' => PostUpdateCommand::class]);
        $form->submit([], false);

        if (!$form->isValid()) {
            throw new FormValidErrorException();
        }

        $this->commandBus->handle($command);

        return new JsonResponse(
            $this->serializer->serialize(
                new PostRepresentation($post),
                'json',
                SerializationContext::create()->setGroups(['post_update'])->setSerializeNull(true)
            ),
            JsonResponse::HTTP_OK,
            [],
            true
        );
    }

    /**
     * @OA\Delete(
     *     tags={"Post"},
     *     summary="Delete post",
     *     @OA\Response(
     *         response="201",
     *         description="Post deleted"
     *     ),
     *     @OA\Response(
     *         response="401",
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response="403",
     *         description="Access denied"
     *     ),
     *     @OA\Response(
     *         response="500",
     *         description="Internal server error"
     *     )
     * )
     *
     * @ParamConverter("post", converter="post")
     *
     * @param Post $post
     *
     * @return JsonResponse
     */
    public function deletePostAction(Post $post): JsonResponse
    {
        $command = new PostDeleteCommand();
        $command->post = $post;

        return new JsonResponse($this->commandBus->handle($command));
    }
}
