<?php

namespace App\Http\Controllers;

use App\Models\CollaborativeProject;
use App\Models\Invitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    /**
     * Retrieve all pending invitations for the logged-in user.
     * e.g. GET /api/my-invitations
     */
    public function indexPendingInvitations()
    {
        // Only fetch invitations where 'invitee_id' is the current user and 'status' = 'pending'
        // Also eager-load 'project' and 'inviter' if you want their data in the JSON response
        $invitations = Invitation::with(['project', 'inviter'])
            ->where('invitee_id', Auth::id())
            ->where('status', 'pending')
            ->get();

        return response()->json($invitations);
    }

    /**
     * Invite a specific user to collaborate on the project.
     * e.g. /api/projects/{projectId}/invite
     */
    public function invite(Request $request, $projectId)
    {
        $request->validate([
            'invitee_id' => 'required|exists:users,id'
        ]);

        $project = CollaborativeProject::findOrFail($projectId);

        // Only project owner can send invites
        if ($project->owner_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Create an invitation
        $invite = Invitation::create([
            'project_id' => $projectId,
            'inviter_id' => Auth::id(),
            'invitee_id' => $request->invitee_id,
            'status'     => 'pending'
        ]);

        return response()->json([
            'message' => 'Invitation sent.',
            'invitation' => $invite
        ], 201);
    }

    /**
     * Accept or reject an invitation.
     * e.g. POST /api/invitations/{id}/respond
     */
    public function respond(Request $request, $invitationId)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected'
        ]);

        $invite = Invitation::findOrFail($invitationId);

        // Only the invitee can respond to the invitation
        if ($invite->invitee_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $invite->update(['status' => $request->status]);

        if ($request->status === 'accepted') {
            // Add row in project_collaborators
            \App\Models\ProjectCollaborator::create([
                'project_id' => $invite->project_id,
                'user_id'    => Auth::id(),
                'role'       => 'designer', // or pass role from request
                'joined_at'  => now(),
            ]);
        }

        return response()->json([
            'message' => 'Invitation ' . $request->status . '.',
            'invitation' => $invite
        ]);
    }

    /**
     * Request to join a project (user-initiated).
     * e.g. /api/projects/{projectId}/request-join
     */
    public function requestJoin($projectId)
    {
        $project = CollaborativeProject::findOrFail($projectId);

        // Create an 'invitation' but from the perspective of the user requesting
        $invite = Invitation::create([
            'project_id' => $projectId,
            'inviter_id' => null,  // no direct inviter
            'invitee_id' => Auth::id(),
            'status'     => 'pending'
        ]);

        return response()->json([
            'message' => 'Join request sent.',
            'invitation' => $invite
        ]);
    }

    public function indexSentInvitations()
    {
        // Retrieve all invitations you sent, regardless of status
        $invitations = Invitation::with(['project', 'invitee'])
            ->where('inviter_id', Auth::id())
            ->get();
    
        return response()->json($invitations);
    }
    

    public function destroy($id)
{
    $invitation = Invitation::findOrFail($id);
    
    // Optionally, restrict deletion to the inviter
    if ($invitation->inviter_id !== Auth::id()) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }
    
    $invitation->delete();
    
    return response()->json(['message' => 'Invitation deleted successfully.']);
}

    /**
     * Project owner reviews (accept/reject) a join request
     * e.g. POST /api/invitations/{id}/owner-respond
     */
    public function ownerRespondToRequest(Request $request, $invitationId)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected'
        ]);

        $invite = Invitation::with('project')->findOrFail($invitationId);

        // Only the project owner can accept/reject a request
        if ($invite->project->owner_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $invite->update(['status' => $request->status]);

        if ($request->status === 'accepted') {
            // Add them to project_collaborators
            \App\Models\ProjectCollaborator::create([
                'project_id' => $invite->project_id,
                'user_id'    => $invite->invitee_id,
                'role'       => 'designer', // or optional
                'joined_at'  => now(),
            ]);
        }

        return response()->json([
            'message' => 'User has been ' . $request->status,
            'invitation' => $invite
        ]);
    }
}
