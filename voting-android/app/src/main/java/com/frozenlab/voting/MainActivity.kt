package com.frozenlab.voting

import android.content.BroadcastReceiver
import android.content.Context
import android.content.Intent
import android.content.IntentFilter
import android.graphics.Rect
import android.os.Bundle
import android.os.Handler
import android.view.MotionEvent
import android.view.inputmethod.InputMethodManager
import android.widget.EditText
import android.widget.ProgressBar
import androidx.appcompat.app.AlertDialog
import androidx.appcompat.app.AppCompatActivity
import androidx.core.app.ActivityCompat
import androidx.core.view.isVisible
import androidx.localbroadcastmanager.content.LocalBroadcastManager
import com.bluelinelabs.conductor.Conductor
import com.bluelinelabs.conductor.Router
import com.frozenlab.api.ApiCommunicator
import com.frozenlab.api.ApiError
import com.frozenlab.api.ApiHolder
import com.frozenlab.api.toApiError
import com.frozenlab.extensions.*
import com.frozenlab.marketplace.api.requests.FCMRequest
import com.frozenlab.voting.api.VotingApi
import com.frozenlab.voting.api.VotingApiContext
import com.frozenlab.voting.api.models.Meeting
import com.frozenlab.voting.api.models.RealEstate
import com.frozenlab.voting.api.models.UserProfile
import com.frozenlab.voting.conductor.controllers.LoginController
import com.frozenlab.voting.conductor.controllers.MainHostController
import com.frozenlab.voting.custom.showAlertOkButton
import com.frozenlab.voting.databinding.ActivityMainBinding
import io.reactivex.rxjava3.android.schedulers.AndroidSchedulers
import io.reactivex.rxjava3.core.Completable
import io.reactivex.rxjava3.core.Single
import io.reactivex.rxjava3.disposables.CompositeDisposable
import io.reactivex.rxjava3.schedulers.Schedulers
import kotlin.system.exitProcess

class MainActivity : AppCompatActivity(), VotingApiContext, ApiCommunicator {

    companion object {

        const val REQUEST_PERMISSIONS_CODE = 3757
        const val KEY_SAVED_CURRENT_USER_PROFILE  = "CurrentUserProfile"
        const val INTENT_NEW_FCM_TOKEN = "VotingNewFCMToken"
        const val USER_DATA_LOADED_CHECK_INTERVAL = 500L // Milliseconds
        const val LOGO_LOADING_DELAY = 2000L // Milliseconds
    }

    var accessToken: String = Preferences.accessToken
        set(value) {
            field = value
            Preferences.accessToken = value
            apiHolder.setHeader("Authorization", "Bearer $value")
        }

    private val apiHolder = ApiHolder(
        VotingApi::class.java,
        Preferences.baseURL,
        hashMapOf(Pair("Authorization", "Bearer $accessToken")),
        hashMapOf(
            Pair(Meeting::class.java, Meeting.Deserializer()),
            Pair(UserProfile::class.java, UserProfile.Serializer()),
            Pair(UserProfile::class.java, UserProfile.Deserializer())
        ),
        Preferences.jsonDateFormat,
        60,
        BuildConfig.DEBUG
    )

    override val votingApi = apiHolder.api as VotingApi

    private val compositeDisposable: CompositeDisposable = CompositeDisposable()

    private val logoHandler = Handler()
    private var logoLoaded  = false
    private var loginFailed = false

    private var userProfileLoaded      = false
    private var userRealEstatesLoaded  = false

    private val userDataLoadedHandler: Handler = Handler()

    private val loadingIndicator: AlertDialog by lazy {
        AlertDialog.Builder(this)
            .setView(ProgressBar(this))
            .create()
    }

    var currentUserProfile: UserProfile? = null
        set(value) {
            field = value
            updateUserProfileViews()
        }

    var currentUserRealEstates: ArrayList<RealEstate>? = null

    private var afterPermissionGrantedCallback: (() -> Unit)? = null

    private lateinit var router: Router

    private var _binding: ActivityMainBinding? = null
    val binding: ActivityMainBinding
        get() { return _binding!! }

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        _binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        router = Conductor.attachRouter(this, binding.controllerContainer, savedInstanceState)

        if(!router.hasRootController() || accessToken.isBlank()) {
            startLogoLoading()
            login()
        }
    }

    override fun onDestroy() {
        super.onDestroy()
        userDataLoadedHandler.removeCallbacks(userDataLoadedChecker)
        compositeDisposable.clear()
    }

    override fun onStart() {
        super.onStart()

        val intentFilter = IntentFilter().apply {
            addAction(INTENT_NEW_FCM_TOKEN)
        }

        LocalBroadcastManager.getInstance(this)
            .registerReceiver(broadcastMessagesReceiver, intentFilter)
    }

    override fun onStop() {
        super.onStop()

        LocalBroadcastManager.getInstance(this)
            .unregisterReceiver(broadcastMessagesReceiver)
    }

    override fun onBackPressed() {

        if(!router.handleBack()) {
            super.onBackPressed()
        }
    }

    override fun dispatchTouchEvent(ev: MotionEvent?): Boolean {

        if(ev == null)
            return super.dispatchTouchEvent(ev)

        if(ev.action == MotionEvent.ACTION_DOWN) {

            val view = currentFocus
            if(view is EditText) {

                val outRect = Rect()
                view.getGlobalVisibleRect(outRect)

                if (!outRect.contains(ev.x.toInt(), ev.y.toInt())) {
                    view.clearFocus()
                    val imm = getSystemService(INPUT_METHOD_SERVICE) as InputMethodManager
                    imm.hideSoftInputFromWindow(view.windowToken, 0)
                }
            }
        }

        return super.dispatchTouchEvent(ev)
    }

    override fun onSaveInstanceState(outState: Bundle) {

        currentUserProfile?.also {
            outState.putAsJson(KEY_SAVED_CURRENT_USER_PROFILE, it, UserProfile::class.java, UserProfile.Serializer())
        }

        super.onSaveInstanceState(outState)
    }

    override fun onRestoreInstanceState(savedInstanceState: Bundle) {

        savedInstanceState.getFromJson<UserProfile>(KEY_SAVED_CURRENT_USER_PROFILE, UserProfile::class.java, UserProfile.Deserializer())?.also {
            currentUserProfile = it
        }

        super.onRestoreInstanceState(savedInstanceState)
    }

    override fun apiRequest(completable: Completable, successUnit: (() -> Unit)?, failUnit: ((throwable: Throwable) -> Unit)?, showLoading: Boolean) {

        if(showLoading)
            showLoadingIndicator(true)

        compositeDisposable.add(
            completable
                .subscribeOn(Schedulers.io())
                .observeOn(AndroidSchedulers.mainThread())
                .doFinally {
                    if(showLoading)
                        showLoadingIndicator(false)
                }
                .subscribe(successUnit, failUnit ?: { throwable -> onReceiveFail(throwable) } )
        )
    }

    override fun <T> apiRequest(single: Single<T>, successUnit: ((param: T) -> Unit)?, failUnit: ((throwable: Throwable) -> Unit)?, showLoading: Boolean) {

        if(showLoading)
            showLoadingIndicator(true)

        compositeDisposable.add(
            single
                .subscribeOn(Schedulers.io())
                .observeOn(AndroidSchedulers.mainThread())
                .doFinally {
                    if(showLoading)
                        showLoadingIndicator(false)
                }
                .subscribe(successUnit, failUnit ?: { throwable -> onReceiveFail(throwable) } )
        )
    }

    //
    // Public area
    //

    fun login() {

        loginFailed            = false
        userProfileLoaded      = false
        userRealEstatesLoaded = false

        userDataLoadedHandler.post(userDataLoadedChecker)

        if(accessToken.isNotBlank()) {

            applyFCMToken()

            getCurrentUserProfile()
            getCurrentUserRealEstates()

        } else {
            loginFailed = true
        }
    }

    fun logout() {

        removeFCMToken()

        Handler().postDelayed({
            this.accessToken = ""
        }, 300L)

        currentUserProfile  = null

        router.setRootFade(LoginController())
    }

    fun requestPermissionsIfNeed(requiredPermissions: ArrayList<String>, afterGrantedCallback: (() -> Unit)? = null): Boolean {

        val notGrantedPermissions = getNotGrantedPermissions(requiredPermissions)

        if(notGrantedPermissions.size > 0) {
            afterPermissionGrantedCallback = afterGrantedCallback
            ActivityCompat.requestPermissions(this, notGrantedPermissions.toTypedArray(), REQUEST_PERMISSIONS_CODE)
            return true
        }

        return false
    }

    //
    // Private area
    //

    private val broadcastMessagesReceiver: BroadcastReceiver = object : BroadcastReceiver() {

        override fun onReceive(context: Context?, intent: Intent?) {

            when(intent?.action) {

                INTENT_NEW_FCM_TOKEN -> {
                    applyFCMToken()
                }
            }
        }
    }

    private val userDataLoadedChecker = object: Runnable {
        override fun run() {

            if(!logoLoaded) {
                userDataLoadedHandler.postDelayed(this, USER_DATA_LOADED_CHECK_INTERVAL)
                return
            }

            if(loginFailed) {

                binding.imageLogo.isVisible = false

                userDataLoadedHandler.removeCallbacks(this)

                if(router.backstack.isEmpty() || (router.backstack.first().controller !is LoginController)) {
                    router.setRootInstant(LoginController())
                }

                return
            }

            if(userProfileLoaded && userRealEstatesLoaded) {

                binding.imageLogo.isVisible = false

                userDataLoadedHandler.removeCallbacks(this)

                router.setRootFade(MainHostController())

                return
            }

            userDataLoadedHandler.postDelayed(this, USER_DATA_LOADED_CHECK_INTERVAL)
        }
    }

    private val logoProgressRequest = Completable.create {

        logoHandler.postDelayed({
            it.onComplete()
        }, LOGO_LOADING_DELAY)
    }

    private fun startLogoLoading() {

        binding.imageLogo.isVisible = true

        logoLoaded = false

        this.apiRequest(
            logoProgressRequest,
            successUnit = {
                logoLoaded = true
            },
            showLoading = false
        )
    }

    private fun getCurrentUserProfile() {

        this.apiRequest(
            votingApi.getUserProfile(),
            { userProfile ->
                userProfileLoaded  = true
                currentUserProfile = userProfile
            },
            { handleThrowable(it, true) },
            false
        )
    }

    private fun getCurrentUserRealEstates() {

        this.apiRequest(
            votingApi.getRealEstates(),
            { list ->
                userRealEstatesLoaded  = true
                currentUserRealEstates = list
            },
            { handleThrowable(it, true) },
            false
        )
    }

    private fun applyFCMToken() {
        val token = Preferences.fcmToken.ifEmpty { null }
        if(!token.isNullOrBlank())
            sendFCMTokenToServer(token, token)
    }

    private fun removeFCMToken() {
        val token = Preferences.fcmToken.ifEmpty { null }
        if(!token.isNullOrBlank())
            sendFCMTokenToServer(token, null)
    }

    private fun sendFCMTokenToServer(oldToken: String?, newToken: String?) {

        val fcmRequest = FCMRequest().apply {
            this.newToken = newToken
            this.oldToken = oldToken
        }

        apiRequest(
            votingApi.sendFCMToken(fcmRequest),
            successUnit = {
                if(!fcmRequest.newToken.isNullOrEmpty()) {
                    Preferences.fcmToken = fcmRequest.newToken!!
                }
            },
            showLoading = false
        )
    }

    private fun showLoadingIndicator(show: Boolean) {

        if (show) {
            loadingIndicator.show()
            loadingIndicator.window?.setBackgroundDrawable(null)
        } else {
            loadingIndicator.hide()
        }
    }

    private fun onReceiveFail(throwable: Throwable) {
        handleThrowable(throwable, false)
    }

    private fun handleThrowable(throwable: Throwable, exitOnNetworkError: Boolean = false) {

        val apiError = throwable.toApiError()

        when(apiError.code) {
            ApiError.ERROR_NETWORK_CONNECTION_FAILED -> {
                if(!exitOnNetworkError) {
                    this.showToast(R.string.error_network_connection_failed)
                } else {

                    if(!router.hasRootController()) {
                        binding.layoutRestoreConnection.isVisible = true
                        binding.buttonRepeat.setOnClickListener {
                            binding.layoutRestoreConnection.isVisible = false
                            startLogoLoading()
                            login()
                        }

                    } else {
                        this.showAlertOkButton(R.string.error_network_connection_failed) {
                            exitProcess(0)
                        }
                    }
                }
            }
            ApiError.ERROR_HTTP_UNAUTHORIZED -> {
                loginFailed = true
            }
            else -> this.showToast(apiError.message, true)
        }
    }

    private fun updateUserProfileViews() {

    }
}