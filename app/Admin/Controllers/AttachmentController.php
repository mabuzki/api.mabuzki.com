<?php

namespace App\Admin\Controllers;

use App\Modals\Attachment;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;

function get_avatar($id) {
	$id = abs(intval($id));
	$id = sprintf("%09d", $id);
	$dir1 = substr($id, 0, 3);
	$dir2 = substr($id, 3, 2);
	$dir3 = substr($id, 5, 2);
	return $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($id, -2)."_avatar.png";
}

class AttachmentController extends Controller
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
        $grid = new Grid(new Attachment);

        $grid->id('Id');
        $grid->username('上传者');
        $grid->userid('上传者uid');
        $grid->type('类型');
        $grid->filepath('路径');
        $grid->filename('文件名');
        $grid->salt('Salt');
        $grid->suffix('类型');
        $grid->animated('动图');
        $grid->width('宽');
        $grid->height('高');
        $grid->postip('PostIP');
        $grid->upload_time('上传时间');

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
        $show = new Show(Attachment::findOrFail($id));



        return $show;
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        $form = new Form(new Attachment);



        return $form;
    }
}
