<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Page;

class PageController extends Controller
{
	public function getPages($chapter_id, Request $request)
	{
		$pages = Page::where('chapter_id', $chapter_id)->get();

		return response()->json(['success' => true, 'data' => $pages]);
	}

	public function move(Request $request)
	{
		$move_order = $request->input('move_order');
		$pages = Page::where('chapter_id', $request->input('chapter_id'))->get();
		$cpage = $pages->where('page_num', $request->input('page_num'))->first();
		$bpage = $pages->where('page_num', $cpage->page_num + $move_order)->first();

		if ($cpage && $bpage) {
			$bpage->page_num -= $move_order;
			$cpage->page_num += $move_order;

			$bpage->save();
			$cpage->save();

			return response()->json(['success' => true, 'message' => '']);
		}

		return response()->json(['success' => false, 'message' => 'Page Not Found', 'data' => $request->all()]);
	}

	public function delete(Request $request)
	{
		$page = Page::where('chapter_id', $request->input('chapter_id'))->where('page_num', $request->input('page_num'))->first();

		if ($page) {
			$page->delete();
			$this->reorderPage($request->input('chapter_id'));

			return response()->json(['success' => true]);
		}

		return response()->json(['succes' => false]);
	}

	public function reorderPage($chapter_id)
	{
		$i = 1;
		$pages = Page::where('chapter_id', $chapter_id)->orderBy('page_num')->get();

		foreach ($pages as $page) {
			if ($page->page_num != $i) {
				$page->page_num = $i;
				$page->save();
			}

			$i++;
		}
	}
}