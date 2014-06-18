<?php

class TrackUserController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		/* select all users in track_user table who wants to be tracked
			user who wants to be tracked will have status set as 1
			select by the max(id
		*/
		/*$track_id = TrackUser:: where('status', '=', '1')
								->orderBy('created_at', 'DESC')
								->select(DB::raw('*, max(id) as id'))
								->orderBy('id', 'desc')
								->groupBy('track_id')
								->get();*/
		$track_id = DB::select(DB::raw("SELECT t.* FROM track_user t 
										JOIN 
										( SELECT track_id, latitude, longitude, MAX(id) maxId
										  FROM track_user WHERE status != 0 GROUP BY track_id
										) t2
										ON t.id = t2.maxId AND t.track_id = t2.track_id"));
		
		return $track_id;
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		$user = new TrackUser;
		$user->track_id = Request::get('trackid');
		$user->latitude = Request::get('lat');
		$user->longitude = Request::get('lng');
		$user->status = Request::get('status');
		$user->save();

		return Response::json(array(
			'error' => false),
			200
		);
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$user = TrackUser::where('track_id', '=', $id)->get();
		return Response::json(array($user, 200));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}


}
