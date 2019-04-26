<?php

namespace App\Admin\Controllers;

use App\Modals\Article;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

class ArticleController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        return $content
            ->header('Index')
            ->description('description')
            ->body($this->grid());
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('Create')
            ->description('description')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new Article);

        $grid->id('Id');
        $grid->author('作者');
        $grid->authorid('作者uid');
        $grid->type('类型');
        $grid->subject('主题');
        $grid->location('位置');
        $grid->cover('封面');
        $grid->content('正文');
        $grid->attachment('附件');
        $grid->tags('标签');
        $grid->readtimes('读取');
        $grid->favtimes('喜欢');
        $grid->replynum('回复');
        $grid->date_post('发布时间');
        $grid->date_update('编辑时间');
        $grid->status('状态');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(Article::findOrFail($id));

        $show->id('Id');
        $show->author('Author');
        $show->authorid('Authorid');
        $show->type('Type');
        $show->subject('Subject');
        $show->location('Location');
        $show->cover('Cover');
        $show->content('Content');
        $show->attachment('Attachment');
        $show->tags('Tags');
        $show->readtimes('Readtimes');
        $show->favtimes('Favtimes');
        $show->replynum('Replynum');
        $show->date_post('Date post');
        $show->date_update('Date update');
        $show->status('Status');

        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Article);

        $form->text('author', 'Author');
        $form->text('authorid', 'Authorid');
        $form->text('type', 'Type');
        $form->text('subject', 'Subject');
        $form->text('location', 'Location');
        $form->image('cover', 'Cover');
        $form->textarea('content', 'Content');
        $form->file('attachment', 'Attachment');
        $form->text('tags', 'Tags');
        $form->text('readtimes', 'Readtimes');
        $form->text('favtimes', 'Favtimes');
        $form->text('replynum', 'Replynum');
        $form->text('date_post', 'Date post');
        $form->text('date_update', 'Date update');
        $form->switch('status', 'Status');

        return $form;
    }
}
