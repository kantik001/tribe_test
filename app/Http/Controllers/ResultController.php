<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberRequest;
use App\Http\Requests\ResultRequest;
use App\Models\Member;
use App\Models\Result;

class ResultController extends Controller
{

    public function store(ResultRequest $request)
    {
        $data = $request->validated();

        $member = Member::where('email', $data['email'])->first();

        if (!$member) {
            $member = new Member([
                'email' => $data['email'],
            ]);
            $member->save();
        }

        $result = new Result([
            'member_id' => $member->id,
            'milliseconds' => $data['milliseconds'],
        ]);

        $result->save();

        return response()->json(['success' => true]);
    }

    public function index(MemberRequest $request)
    {
        $email = $request->input('email');

        $results = Result::where('member_id', '!=', null)
            ->orderBy('milliseconds', 'asc')
            ->get();

        $top = [];
        $self = [];

        foreach ($results as $result) {
            $member = Member::where('id', $result->member_id)->first();

            if ($member) {
                $member->email = str_replace('@', '*****', $member->email);
                $member->email = str_replace('.', '*****', $member->email);
            }

            $top[] = [
                'email' => $member ? $member->email : 'null',
                'place' => count($top) + 1,
                'milliseconds' => $result->milliseconds,
            ];

            if ($email && $member && $member->email == $email) {
                $self = [
                    'email' => $member->email,
                    'place' => count($top) + 1,
                    'milliseconds' => $result->milliseconds,
                ];
            }
        }

        return response()->json(['data' => ['top' => $top, 'self' => $self]]);
    }


}
