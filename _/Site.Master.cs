using CorazonHeart;
using System;
using System.Collections.Generic;
using System.Security.Claims;
using System.Security.Principal;
using System.Text.RegularExpressions;
using System.Web;
using System.Web.Security;
using System.Web.UI;
using System.Web.UI.WebControls;
using System.Data;


namespace ISTMADCAMER
{
    public partial class SiteMaster : MasterPage
    {
        protected void Page_Load(object sender, EventArgs e)
        {
            // init language
            Corazon corazon = Corazon.Current;
            if (corazon.Services.Language.PreferredLanguage == CLanguage.Language.French)
                frenchListItem.Attributes.Add("class", "active");

            else englishListItem.Attributes.Add("class", "active");
        }

        protected void EnglishLanguageLB_Click(object sender, EventArgs e)
        {

        }

        protected void frenchLanguageLB_Click(object sender, EventArgs e)
        {
            // redirect to same page passing lang=enpref
            string currentUrl = Request.RawUrl;

            // get the index of question mark in the URL, if any
            int hasQuestionMark = Request.RawUrl.IndexOf("?");

            // if URL has question mark, remove all query strings
            // this assumes that the website remains static as it is
            // and no query string can be passed except language
            if (hasQuestionMark != -1)
            {
                currentUrl = currentUrl.Substring(0, hasQuestionMark);
            }

            // add qs for new preferred language
            currentUrl = currentUrl + "?lang=fr";

            // redirect / refresh
            Response.Redirect(currentUrl);
        }


        protected void englishLanguageLB_Click(object sender, EventArgs e)
        {
            // redirect to same page passing lang=enpref
            string currentUrl = Request.RawUrl;

            // get the index of question mark in the URL, if any
            int hasQuestionMark = Request.RawUrl.IndexOf("?");

            // if URL has question mark, remove all query strings
            // this assumes that the website remains static as it is
            // and no query string can be passed except language
            if (hasQuestionMark != -1)
            {
                currentUrl = currentUrl.Substring(0, hasQuestionMark);
            }

            // add qs for new preferred language
            currentUrl = currentUrl + "?lang=en";

            // redirect / refresh
            Response.Redirect(currentUrl);
        }

        protected void subscribeBtn_Click(object sender, EventArgs e)
        {

            // display the feedback panel
            feedbackPanel.Visible = true;

            // get the details
            //string name = subscribeNameTB.Text;
            string email = subscribeEmailTB.Text;

            // validate inputs are not empty
            if (String.IsNullOrWhiteSpace(email))
            {
                // inputs cannot be empty
                feedbackPanel.CssClass = "alert alert-danger";
                feedbackMsgLbl.Text = "Please input your email.";
                return;
            }

            // validate email
            Regex regex = new Regex(@"^([\w\.\-]+)@([\w\-]+)((\.(\w){2,3})+)$");
            Match match = regex.Match(email);
            if (!match.Success)
            {
                // email format not valid
                feedbackPanel.CssClass = "alert alert-danger";
                feedbackMsgLbl.Text = "Email format is not valid.";
                return;
            }

            // get the app object
            Fefeo app = Fefeo.Current;

            // validate for duplicates
            DataTable dt = app.User.GetAll(UserType.SubcribedUser);
            bool hasDuplicate = false;
            foreach (DataRow dr in dt.Rows)
            {
                if (dr["Email"].ToString().ToLower() == email.ToLower())
                {
                    hasDuplicate = true;
                    break;
                }
            }
            if (hasDuplicate)
            {
                // inputs cannot be empty
                feedbackPanel.CssClass = "alert alert-danger";
                feedbackMsgLbl.Text = "You have already subscribed with us.";
                return;
            }

            // insert into db
            int? userID = app.User.Add(UserType.SubcribedUser, null, email);

            bool isSuccess = userID != null;

            if (isSuccess)
            {
                // display email sent successfully
                feedbackPanel.CssClass = "alert alert-success";
                feedbackMsgLbl.Text = "Thank you! Subscription successful.";


                //ClientScript.RegisterClientScriptBlock(this.GetType(), "alert",
                // "swal('Good job!', 'You clicked Success button!', 'success')", true);

                // clear inputs
                subscribeEmailTB.Text = "";
                //subscribeNameTB.Text = "";
            }
            else
            {
                // inputs cannot be empty
                feedbackPanel.CssClass = "alert alert-danger";
                feedbackMsgLbl.Text = "We are not able to subscribe you now. Try again later.";
            }
        }
    }
}