<?php

namespace Coyote\Http\Forms\Forum;

use Coyote\Repositories\Contracts\ForumRepositoryInterface as ForumRepository;
use Coyote\Repositories\Contracts\GroupRepositoryInterface as GroupRepository;
use Coyote\Services\FormBuilder\Form;

class ForumForm extends Form
{
    /**
     * @var ForumRepository
     */
    protected $forum;

    /**
     * @var GroupRepository
     */
    protected $group;

    /**
     * @param ForumRepository $forum
     * @param GroupRepository $group
     */
    public function __construct(ForumRepository $forum, GroupRepository $group)
    {
        parent::__construct();

        $this->forum = $forum;
        $this->group = $group;
    }

    public function buildForm()
    {
        $this
            ->add('name', 'text', [
                'label' => 'Nazwa',
                'rules' => 'required|string|max:50'
            ])
            ->add('parent_id', 'select', [
                'label' => 'Kategoria macierzysta',
                'choices' => $this->getParentList(),
                'empty_value' => '--'
            ])
            ->add('description', 'textarea', [
                'label' => 'Opis',
                'rules' => 'required|string|max:255'
            ])
            ->add('section', 'text', [
                'label' => 'Nazwa sekcji',
                'rules' => 'string|max:50'
            ])
            ->add('is_locked', 'checkbox', [
                'label' => 'Forum zablokowane'
            ])
            ->add('require_tag', 'checkbox', [
                'label' => 'Wymagaj podania co najmniej 1 tagu'
            ])
            ->add('enable_reputation', 'checkbox', [
                'label' => 'Zliczaj reputację w tej kategorii'
            ])
            ->add('enable_anonymous', 'checkbox', [
                'label' => 'Zezwalaj na tworzenie postów bez logowania'
            ])
            ->add('access', 'choice', [
                'label' => 'Dostęp dla grup',
                'choices' => $this->getGroupsList(),
                'property' => 'group_id'
            ])
            ->add('submit', 'submit', [
                'label' => 'Zapisz',
                'attr' => [
                    'data-submit-state' => 'Wysyłanie...'
                ]
            ]);
    }

    /**
     * @return array
     */
    private function getParentList()
    {
        return $this->forum->whereNull('parent_id')->lists('name', 'id')->toArray();
    }

    /**
     * @return array
     */
    private function getGroupsList()
    {
        return $this->group->lists('name', 'id');
    }
}