<?php

namespace App\Http\Controllers;

use App\Category;
use App\ChildCate;
use App\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use function PHPSTORM_META\map;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');

        //TODO group categories
        $htmlCate = "";
        $cates = DB::table('categories')
            ->leftjoin('child_cates', 'categories.id', '=', 'child_cates.id')
            ->where('child_cates.lvl', 0)
            ->get();
        foreach ($cates as $cate) {
            if ($cate) {
                $htmlCate .= '<li class="nav__dropdown">';
                $htmlCate .= '<a href="#">' . $cate->name . '</a>';
                $childs = DB::table('categories')
                    ->leftjoin('child_cates', 'categories.id', '=', 'child_cates.id')
                    ->where('child_cates.lvl', $cate->id)
                    ->get();
                $htmlCate .= '<ul class="nav__dropdown-menu">';
                foreach ($childs as $child) {
                    if ($child) {
                        $htmlCate .= '<li><a href="'.route('catePost',[$child->id,$child->alias]).'">' . $child->name . '</a></li>';
                    }
                }
                $htmlCate .= '</ul>';
                $htmlCate .= '</li>';
            }
        }

        view()->share(['cates' => $htmlCate]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $htmlNews = "";
        $htmlnewNews = "";
        $htmlPostList = "";
        $htmlPost = "";
        $infoCateNews = array();

        //TODO group news
        $news = DB::table('news AS ne')
            ->leftjoin('categories AS cat', 'cat.id', '=', 'ne.Cate_id')
            ->select('cat.id as cateID', 'ne.id as newsID', 'cat.name', 'cat.alias AS cateSlug', 'ne.title', 'ne.alias', 'ne.summary', 'ne.description', 'ne.active', 'ne.images', 'ne.created_at')
            ->where([
                ['ne.active', 1],
                ['ne.hot', 1]
            ])
            ->orderBy('newsID', 'DESC')
            ->take(2)
            ->get();
        foreach ($news as $new) {
            if ($new) {
                $htmlNews .= '<div class="col-md-6">';
                $htmlNews .= '<article class="entry">';
                $htmlNews .= '<div class="entry__img-holder">';
                $htmlNews .= '<a href="' . route('postNews', [$new->newsID, $new->alias]) . '">';
                $htmlNews .= '<div class="thumb-container thumb-75">';
                $htmlNews .= '<img data-src="' . asset('upload/thumbnail/' . $new->images) . '" src="' . asset('upload/thumbnail/' . $new->images) . '" class="entry__img lazyload" alt="' . $new->title . '" />';
                $htmlNews .= '</div>';
                $htmlNews .= '</a>';
                $htmlNews .= '</div>';
                $htmlNews .= '<div class="entry__body">';
                $htmlNews .= '<div class="entry__header">';
                $htmlNews .= '<a href="' . route('catePost', [$new->cateID, $new->cateSlug]) . '" class="entry__meta-category">' . $new->name . '</a>';
                $htmlNews .= '<h2 class="entry__title">';
                $htmlNews .= '<a href="' . route('postNews', [$new->newsID, $new->alias]) . '">' . $new->title . '</a>';
                $htmlNews .= '</h2>';
                $htmlNews .= '<ul class="entry__meta">';
                $htmlNews .= '<li class="entry__meta-date">';
                $htmlNews .= '<i class="ui-date"></i>';
                $htmlNews .= date('d-m-Y', strtotime($new->created_at));
                $htmlNews .= '</li>';
                $htmlNews .= '</ul>';
                $htmlNews .= '</div>';
                $htmlNews .= '<div class="entry__excerpt">';
                $htmlNews .= '<p>' . truncateStringWords($new->summary, 180) . '</p>';
                $htmlNews .= '</div>';
                $htmlNews .= '</div>';
                $htmlNews .= '</article>';
                $htmlNews .= '</div>';
            }

        }
        //TODO group categories hot news and categories tab menu
        $getCate = DB::table('categories as cate')
            ->join('child_cates as chil', 'cate.id', 'chil.cateParen_id', 'cate.name')
            ->where('chil.lvl', 0)
            ->get();

        foreach ($getCate as $sub) {
            //TODO after that, code this for news
            $getNews = DB::table('categories as cate')
                ->join('child_cates as chil', 'cate.id', 'chil.cateParen_id', 'cate.name')
                ->rightjoin('news as ne', 'cate.id', 'ne.Cate_id')
                ->select('cate.id as cateID', 'ne.id as newsID', 'cate.name', 'cate.alias as cateSlug', 'ne.title', 'ne.alias as newsSlug', 'ne.summary', 'ne.images', 'ne.created_at as newsDate')
                ->where([
                    ['chil.lvl', $sub->cateParen_id],
                    ['ne.active', 1],
                    ['ne.hot', 1]
                ])
                ->take(2)
                ->orderBy('ne.id', 'DESC')
                ->get();
            //TODO array info
            if ($getNews) {
                $infoCateNews[] = array(
                    'cateName' => $sub->name,
                    'cateSlug' => $sub->alias,
                    'CateAndNews' => $getNews
                );
            }
        }
        //TODO tin moi
        $getnewNews = DB::table('categories as cate')
            ->join('child_cates as chil', 'cate.id', 'chil.cateParen_id', 'cate.name')
            ->rightjoin('news as ne', 'cate.id', 'ne.Cate_id')
            ->select('cate.id as cateID', 'ne.id as newsID', 'cate.name', 'cate.alias as cateSlug', 'ne.title', 'ne.alias as newsSlug', 'ne.summary', 'ne.images', 'ne.created_at as newsDate')
            ->where([
                ['ne.active', 1],
            ])
            ->take(7)
            ->orderBy('ne.id', 'DESC')
            ->get();
        $getOne = $getnewNews->shift();
        $htmlnewNews .= '<article class="entry">';
        $htmlnewNews .= '<div class="entry__img-holder">';
        $htmlnewNews .= '<a href="' . route('postNews', [$getOne->newsID, $getOne->newsSlug]) . '">';
        $htmlnewNews .= '<div class="thumb-container thumb-75">';
        $htmlnewNews .= '<img data-src="' . asset('upload/thumbnail/' . $getOne->images) . '" src="' . asset('upload/thumbnail/' . $getOne->images) . '" class="entry__img lazyload" alt="' . $getOne->title . '" />';
        $htmlnewNews .= '</div>';
        $htmlnewNews .= '</a>';
        $htmlnewNews .= '</div>';
        $htmlnewNews .= '';
        $htmlnewNews .= '<div class="entry__body">';
        $htmlnewNews .= '<div class="entry__header">';
        $htmlnewNews .= '<a href="' . route('catePost', [$getOne->cateID, $getOne->cateSlug]) . '" class="entry__meta-category">' . $getOne->name . '</a>';
        $htmlnewNews .= '<h2 class="entry__title">';
        $htmlnewNews .= '<a href="' . route('postNews', [$getOne->newsID, $getOne->newsSlug]) . '">' . $getOne->title . '</a>';
        $htmlnewNews .= '</h2>';
        $htmlnewNews .= '<ul class="entry__meta">';
        $htmlnewNews .= '<li class="entry__meta-date">';
        $htmlnewNews .= '<i class="ui-date"></i>';
        $htmlnewNews .= date('d-m-Y', strtotime($getOne->newsDate));
        $htmlnewNews .= '</li>';
        $htmlnewNews .= '</ul>';
        $htmlnewNews .= '</div>';
        $htmlnewNews .= '<div class="entry__excerpt">';
        $htmlnewNews .= '<p>' . truncateStringWords($getOne->summary, 180) . '</p>';
        $htmlnewNews .= '</div>';
        $htmlnewNews .= '</div>';
        $htmlnewNews .= '</article>';

        foreach ($getnewNews as $news) {
            $htmlPostList .= '<li class="post-list-small__item">';
            $htmlPostList .= '<article class="post-list-small__entry">';
            $htmlPostList .= '<div class="post-list-small__body">';
            $htmlPostList .= '<h3 class="post-list-small__entry-title">';
            $htmlPostList .= '<a href="' . route('postNews', [$news->newsID, $news->newsSlug]) . '">' . $news->title . '</a>';
            $htmlPostList .= '</h3>';
            $htmlPostList .= '<ul class="entry__meta">';
            $htmlPostList .= '<li class="entry__meta-date">';
            $htmlPostList .= '<i class="ui-date"></i>';
            $htmlPostList .= date('d-m-Y', strtotime($news->newsDate));
            $htmlPostList .= '</li>';
            $htmlPostList .= '</ul>';
            $htmlPostList .= '</div>';
            $htmlPostList .= '</article>';
            $htmlPostList .= '</li>';
        }

        //TODO post news
        $getAllNews = DB::table('categories as cate')
            ->join('child_cates as chil', 'cate.id', 'chil.cateParen_id', 'cate.name')
            ->select('cate.name', 'cate.alias as cateSlug', 'cate.id')
            ->where('chil.lvl', '<>', 0)
            ->get();
        foreach ($getAllNews as $getFuck) {
            $htmlPost .= '<div class="col-md-6 mb-40">';
            $htmlPost .= '<div class="title-wrap bottom-line bottom-line--orange">';
            $htmlPost .= '<h3 class="section-title section-title--sm">' . $getFuck->name . '</h3>';
            $htmlPost .= '</div>';
            $post = DB::table('news')
                ->select('id', 'title', 'alias', 'summary', 'images', 'created_at')
                ->where([
                    ['news.active', 1],
                    ['news.Cate_id', $getFuck->id]
                ])
                ->get();
            $getOnePost = $post->shift();
            $htmlPost .= '<article class="entry">';
            $htmlPost .= '<div class="entry__img-holder">';
            $htmlPost .= '<a href="' . route('postNews', [$getOnePost->id, $getOnePost->alias]) . '">';
            $htmlPost .= '<div class="thumb-container thumb-75">';
            $htmlPost .= '<img data-src="' . asset('upload/thumbnail/' . $getOnePost->images) . '" src="' . asset('upload/thumbnail/' . $getOnePost->images) . '" class="entry__img lazyload" alt="" />';
            $htmlPost .= '</div>';
            $htmlPost .= '</a>';
            $htmlPost .= '</div>';
            $htmlPost .= '';
            $htmlPost .= '<div class="entry__body">';
            $htmlPost .= '<div class="entry__header">';
            $htmlPost .= '<h2 class="entry__title">';
            $htmlPost .= '<a href="' . route('postNews', [$getOnePost->id, $getOnePost->alias]) . '">' . $getOnePost->title . '</a>';
            $htmlPost .= '</h2>';
            $htmlPost .= '<ul class="entry__meta">';
            $htmlPost .= '<li class="entry__meta-date">';
            $htmlPost .= '<i class="ui-date"></i>';
            $htmlPost .= date('d-m-Y', strtotime($getOnePost->created_at));
            $htmlPost .= '</li>';
            $htmlPost .= '</ul>';
            $htmlPost .= '</div>';
            $htmlPost .= '<div class="entry__excerpt">';
            $htmlPost .= '<p>' . truncateStringWords($getOnePost->summary, 180) . '</p>';
            $htmlPost .= '</div>';
            $htmlPost .= '</div>';
            $htmlPost .= '</article>';
            $htmlPost .= '<ul class="post-list-small post-list-small--border-top">';
            foreach ($post as $ps) {
                $htmlPost .= '<li class="post-list-small__item">';
                $htmlPost .= '<article class="post-list-small__entry">';
                $htmlPost .= '<div class="post-list-small__body">';
                $htmlPost .= '<h3 class="post-list-small__entry-title">';
                $htmlPost .= '<a href="' . route('postNews', [$ps->id, $ps->alias]) . '">' . $ps->title . '</a>';
                $htmlPost .= '</h3>';
                $htmlPost .= '<ul class="entry__meta">';
                $htmlPost .= '<li class="entry__meta-date">';
                $htmlPost .= '<i class="ui-date"></i>';
                $htmlPost .= date('d-m-Y', strtotime($ps->created_at));
                $htmlPost .= '</li>';
                $htmlPost .= '</ul>';
                $htmlPost .= '</div>';
                $htmlPost .= '</article>';
                $htmlPost .= '</li>';
            }
            $htmlPost .= '</ul>';
            $htmlPost .= '</div>';
        }

        return view('workshop.index')->with(['thisNews' => $htmlNews, 'infoNews' => $infoCateNews, 'thisNewNews' => $htmlnewNews, 'thisPostList' => $htmlPostList, 'thisPostNews' => $htmlPost]);
    }

    public function singlepost($id)
    {
        $htmlPost = "";
        $htmlRelate = "";
        $news = DB::table('news as ne')
            ->leftjoin('categories as cate', 'ne.Cate_id', 'cate.id')
            ->select('ne.id as NewsID', 'cate.id as CateID', 'cate.name', 'cate.alias as cateSlug', 'ne.title', 'ne.metaTitle', 'ne.alias', 'ne.summary', 'ne.description', 'ne.content', 'ne.images', 'ne.created_at')
            ->where('ne.id', $id)
            ->get();
        $tags = DB::table('news as ne')
            ->leftjoin('news_tags as ntag', 'ne.id', 'ntag.news_id')
            ->leftjoin('tags as t', 'ntag.tag_id', 't.id')
            ->select('t.title as tagTitle', 't.id as tagID', 't.alias as tagSlug')
            ->where('ne.id', $id)
            ->get();

        $htmlPost .= '<div class="single-post__entry-header entry__header" style="text-align: justify">';
        foreach ($news as $n) {
            $htmlPost .= '<a href="#" class="entry__meta-category">' . $n->name . '</a>';
            $htmlPost .= '<h1 class="single-post__entry-title">' . $n->title . '</h1>';
            $htmlPost .= '<ul class="entry__meta">';
            $htmlPost .= '<li class="entry__meta-date"><i class="ui-date"></i>' . date('d-m-Y', strtotime($n->created_at)) . '</li>';
            $htmlPost .= '</ul>';
            $htmlPost .= '</div>';
            $htmlPost .= '<div class="entry__img-holder">';
            $htmlPost .= '<img src="' . asset('upload/thumbnail/' . $n->images) . '" alt="' . $n->title . '" class="entry__img">';
            $htmlPost .= '</div>';
            $htmlPost .= '<div class="entry__share">';
            $htmlPost .= '<div class="socials entry__share-socials">';
            $htmlPost .= '<a href="#" class="social social-facebook entry__share-social social--wide social--medium">';
            $htmlPost .= '<i class="ui-facebook"></i>';
            $htmlPost .= '<span class="social__text">Share on Facebook</span>';
            $htmlPost .= '</a>';
            $htmlPost .= '<a href="#" class="social social-twitter entry__share-social social--wide social--medium">';
            $htmlPost .= '<i class="ui-twitter"></i>';
            $htmlPost .= '<span class="social__text">Share on Twitter</span>';
            $htmlPost .= '</a>';
            $htmlPost .= '<a href="#" class="social social-google-plus entry__share-social social--medium">';
            $htmlPost .= '<i class="ui-google"></i>';
            $htmlPost .= '</a>';
            $htmlPost .= '<a href="#" class="social social-pinterest entry__share-social social--medium">';
            $htmlPost .= '<i class="ui-pinterest"></i>';
            $htmlPost .= '</a>';
            $htmlPost .= '</div>';
            $htmlPost .= '</div>';
            $htmlPost .= '<div class="entry__article">';
            $htmlPost .= '<p>' . $n->content . '</p>';
            //TODO dung ke CateID
            $relate = DB::table('news as n')
                ->leftjoin('categories as cate', 'n.Cate_id', 'cate.id')
                ->select('n.id as newsID', 'n.title', 'n.alias', 'n.images')
                ->where([
                    ['cate.id', $n->CateID],
                    ['n.id', '<>', $id]
                ])
                ->get();
        }

        $htmlPost .= '<div class="entry__tags">';
        $htmlPost .= '<span class="entry__tags-label">Tags:</span>';
        foreach ($tags as $tag) {
            if ($tag->tagTitle == null) {
                null;
            } else {

                $htmlPost .= '<a href="' . route('tagPost', [$tag->tagID, $tag->tagSlug]) . '" rel="tag">' . $tag->tagTitle . '</a>';
            }
        }
        $htmlPost .= '</div>';
        $htmlPost .= '</div>';

        //TODO Related Post
        $htmlRelate .= '<div class="row row-20">';
        if (count($relate) <> 0) {
            foreach ($relate as $r) {
                $htmlRelate .= '<div class="col-md-4">';
                $htmlRelate .= '<article class="entry">';
                $htmlRelate .= '<div class="entry__img-holder">';
                $htmlRelate .= '<a href="single-post.html">';
                $htmlRelate .= '<div class="thumb-container thumb-75">';
                $htmlRelate .= '<img data-src="' . asset('upload/thumbnail/' . $r->images) . '" src="' . asset('upload/thumbnail/' . $r->images) . '" class="entry__img lazyload" alt="' . $r->title . '">';
                $htmlRelate .= '</div>';
                $htmlRelate .= '</a>';
                $htmlRelate .= '</div>';
                $htmlRelate .= '<div class="entry__body">';
                $htmlRelate .= '<div class="entry__header">';
                $htmlRelate .= '<h2 class="entry__title entry__title--sm">';
                $htmlRelate .= '<a href="' . route('postNews', [$r->newsID, $r->alias]) . '">' . $r->title . '</a>';
                $htmlRelate .= '</h2>';
                $htmlRelate .= '</div>';
                $htmlRelate .= '</div>';
                $htmlRelate .= '</article>';
                $htmlRelate .= '</div>';
            }
        } else {
            null;
        }
        $htmlRelate .= '</div>';

        //TODO Next and Previous news
        $next_id= News::where('id','>',$id)->min('id');
        $previous_id= News::where('id','<',$id)->max('id');
        $next= News::find($next_id);
        $previous= News::find($previous_id);
        return view('workshop.single-post')->with(['thisSinglePost' => $htmlPost, 'thisRelate' => $htmlRelate,'nextNews' => $next,'preNews' => $previous]);
    }

    public function catepost($id, $slug)
    {
        $htmlCate = "";
        $getCate = DB::table('categories')
            ->select('name')
            ->where('id', $id)
            ->get();
        $getNews = DB::table('news as n')
            ->leftjoin('categories as cate', 'n.Cate_id', 'cate.id')
            ->select('cate.name', 'cate.alias as cateSlug', 'cate.id as cateID', 'n.id as newsID', 'n.title', 'n.alias as newsSlug', 'n.summary', 'n.images', 'n.created_at')
            ->where('cate.id', $id)
            ->get();
        foreach ($getNews as $news) {
            $htmlCate .= '<div class="col-md-6">';
            $htmlCate .= '<article class="entry">';
            $htmlCate .= '<div class="entry__img-holder">';
            $htmlCate .= '<a href="' . route('postNews', [$news->newsID, $news->newsSlug]) . '">';
            $htmlCate .= '<div class="thumb-container thumb-75">';
            $htmlCate .= '<img data-src="' . asset('upload/thumbnail/' . $news->images) . '" src="' . asset('upload/thumbnail/' . $news->images) . '" class="entry__img lazyload" alt="' . $news->title . '">';
            $htmlCate .= '</div>';
            $htmlCate .= '</a>';
            $htmlCate .= '</div>';
            $htmlCate .= '<div class="entry__body">';
            $htmlCate .= '<div class="entry__header">';
            $htmlCate .= '<h2 class="entry__title">';
            $htmlCate .= '<a href="' . route('postNews', [$news->newsID, $news->newsSlug]) . '">' . $news->title . '</a>';
            $htmlCate .= '</h2>';
            $htmlCate .= '<ul class="entry__meta">';
            $htmlCate .= '<li class="entry__meta-date">';
            $htmlCate .= '<i class="ui-date"></i>';
            $htmlCate .= date('d-m-Y', strtotime($news->created_at));
            $htmlCate .= '</li>';
            $htmlCate .= '</ul>';
            $htmlCate .= '</div>';
            $htmlCate .= '<div class="entry__excerpt">';
            $htmlCate .= '<p>' . truncateStringWords($news->summary, 180) . '</p>';
            $htmlCate .= '</div>';
            $htmlCate .= '</div>';
            $htmlCate .= '</article>';
            $htmlCate .= '</div>';
        }
        return view('workshop.categories')->with(['thisCategories' => $htmlCate, 'nameCate' => $getCate]);
    }

    public function tagpost($id)
    {
        $htmlTag = "";
        $getTag = DB::table('tags')
            ->select('title')
            ->where('id', $id)
            ->get();
        $getNews = DB::table('news as n')
            ->leftjoin('news_tags as newstag', 'n.id', 'newstag.news_id')
            ->rightjoin('tags as t', 'newstag.tag_id', 't.id')
            ->select('n.id as newsID', 'n.alias as newsSlug', 'n.title', 'n.alias', 'n.summary', 'n.images', 'n.created_at')
            ->where('t.id', $id)
            ->get();
        foreach ($getNews as $news) {
            $htmlTag .= '<div class="col-md-6">';
            $htmlTag .= '<article class="entry">';
            $htmlTag .= '<div class="entry__img-holder">';
            $htmlTag .= '<a href="' . route('postNews', [$news->newsID, $news->newsSlug]) . '">';
            $htmlTag .= '<div class="thumb-container thumb-75">';
            $htmlTag .= '<img data-src="' . asset('upload/thumbnail/' . $news->images) . '" src="' . asset('upload/thumbnail/' . $news->images) . '" class="entry__img lazyload" alt="' . $news->title . '">';
            $htmlTag .= '</div>';
            $htmlTag .= '</a>';
            $htmlTag .= '</div>';
            $htmlTag .= '<div class="entry__body">';
            $htmlTag .= '<div class="entry__header">';
            $htmlTag .= '<h2 class="entry__title">';
            $htmlTag .= '<a href="' . route('postNews', [$news->newsID, $news->newsSlug]) . '">' . $news->title . '</a>';
            $htmlTag .= '</h2>';
            $htmlTag .= '<ul class="entry__meta">';
            $htmlTag .= '<li class="entry__meta-date">';
            $htmlTag .= '<i class="ui-date"></i>';
            $htmlTag .= date('d-m-Y', strtotime($news->created_at));
            $htmlTag .= '</li>';
            $htmlTag .= '</ul>';
            $htmlTag .= '</div>';
            $htmlTag .= '<div class="entry__excerpt">';
            $htmlTag .= '<p>' . truncateStringWords($news->summary, 180) . '</p>';
            $htmlTag .= '</div>';
            $htmlTag .= '</div>';
            $htmlTag .= '</article>';
            $htmlTag .= '</div>';
        }
        return view('workshop.tags')->with(['thisTags' => $htmlTag, 'tags' => $getTag]);
    }

}

