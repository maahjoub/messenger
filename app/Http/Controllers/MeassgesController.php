<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\message;
use App\Models\Recipient;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class MeassgesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $user = Auth::user();
       $conversation = $user->conversations->find($id);
    //    return $conversation;
       return $conversation->theMessage;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'message' => ['required' , 'string'],
            'conversation_id' => [
                Rule::requiredIf(function() use ($request) {
                    return !$request->user_id;
                }),
                'int' , 'exists:conversations,id'
            ],
            'user_id' => [
                Rule::requiredIf(function() use ($request) {
                    return !$request->conversation_id;
                }),
                'int' , 'exists:users,id'
            ] 
            ]);
            $user =  Auth::user();
            $conversation_id = $request->input('conversation_id');
            $user_id = $request->input('user_id');
            DB::beginTransaction();
            try {
                if($conversation_id)
                {
                    $conversation = $user->conversations->findOrFail($conversation_id);
                }else {
                    $conversation = Conversation::where('type', '=',  'peer')
                        ->whereHas('participant', function (Builder $bulder) use ($user_id, $user) {
                        $bulder->join('participants as participants2', 'participants2.conversation_id', '=', 'participants.conversation_id')
                                ->where('participants.user_id', '=', $user_id)
                                ->where('participants2.user_id', '=', $user->id);
                    })->first();
                    if(!$conversation)
                    {
                        $conversation = Conversation::create([
                            'user_id' => $user->id,
                            'type' => 'peer'
                        ]);
                        $conversation->participant()->attach([
                            $user->id => ['joined_at'=> now()], 
                            $user_id  => ['joined_at'=> now()]
                        ]);
                    }
                }
                $message = $conversation->theMessage()->create([
                    'user_id' => $user->id,
                    'body' =>$request->input('message')
                ]);
            
                $conversation->update([
                    'last_message_id' => $message->id
                ]);
                DB::statement('
                    INSERT INTO recipients (user_id, message_id)
                    SELECT user_id , ? FROM participants
                    WHERE conversation_id = ? 
                ', [$message->id , $conversation->id]);
                DB::statement('
                    UPDATE recipients SET red_at = ?
                    WHERE user_id = ? AND message_id = ?
                ', [now(), $message->user_id, $message->id]);
                DB::commit();
                Broadcast(new MessageSent($message));
            } catch (\Throwable $e) {
                DB::rollBack();
                throw $e;
            }
          return $message; 
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Recipient::where([
            'user_id' => Auth::id(),
            'message_id' => $id,
        ])->delete();
        return [
            'message' => 'deleted!'
        ];  
    }
}
